<?php

namespace App\Services;

use App\Models\Odoo\Attributes\Interior;
use App\Models\Odoo\Pricelist;
use App\Models\Odoo\PricelistItem;
use App\Models\Odoo\ProductVariant;
use App\Models\Odoo\Translation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ShippingService
{
    /**
     * We want to add only "business" days
     * but also support possible holidays
     * @param $addWeekdays
     * @return Carbon $date;
     */
    public function calculateDeliveryDate($addWeekdays, $date = null)
    {

        $holidays = ["2015-01-01", "2015-01-02"]; // @TODO: Enum, oid / global config?
        $date = (isset($date)) ? $date : Carbon::now()->toDateString();
        $now = Carbon::parse($date);

        $now->addWeekdays($addWeekdays);

        for ($i = 1; $i <= $addWeekdays; $i++) {
            if (in_array(Carbon::parse($date)->addWeekdays($i)->toDateString(), $holidays)) {
                $now->addDay();
            }
        }

        return $now;
    }

    public function determineOptions($total_quantity)
    {
        $now = Carbon::now();
        $noon_hour = '14:00:00';

        $fanco_available = false;
        $bezorgregeling = false;
        $today_before_noon = false;
        $today_after_noon = false;

        $user = (new \App\Models\Odoo\Partner)->getById(auth()->user()->odoo_user_id, true)->collect();

        $odoo_contact_franco = (isset($user['x_studio_franco_levering_vanaf_1'])) ? true : false;
        $odoo_contact_bezorgregeling = false; // @TODO: Check fields in Odoo

        // Franco delivery
        if ($odoo_contact_franco) {
            $franco_available = true;
        }
        // Bezorg regeling
        if ($odoo_contact_bezorgregeling) {
            $bezorgregeling = true;
            // Get product from Odoo, and return it
        }
        // If today and before noon
        if ($now->lessThan(Carbon::parse($now->toDateString() . ' ' . $noon_hour)) && $now->isToday()) {
            $today_before_noon = true;
        }
        // Today after noon
        if ($now->greaterThan(Carbon::parse($now->toDateString() . ' ' . $noon_hour)) && $now->isToday()) {
            $today_after_noon = true;
        }

        return [
            'franco' => $fanco_available,
            'bezorgregeling' => $bezorgregeling,
            'before_noon' => $today_before_noon,
            'after_noon' => $today_after_noon,
        ];
    }

    /**
     * @param array $product_ids
     * @param $total_quantity
     * @return bool[]
     */
    public function deliveryOptions($product_ids = [], $total_quantity = 1)
    {
        if( empty(Auth::user()->odoo_user_id) ){
            throw ValidationException::withMessages(['Your user doesn\'t have a company connected. Please contact an admin.', 'return' => true]);
        }

        setlocale(LC_TIME, 'nl_NL');
        Carbon::setLocale('nl_NL');

        $product_data = (new ProductVariant())->all();
        $products = $product_data->whereIn('id', $product_ids);

        $longest_time = $products->pluck('produce_delay')->max();

        $options = $this->determineOptions($total_quantity);

        if ($options['franco']) { // Option A

            if ($options['before_noon']) {
                $date = $this->calculateDeliveryDate(2);
            }

            if ($options['after_noon']) {
                $date = $this->calculateDeliveryDate(3);
            }

            $deliveryOptions[] = [
                'name' => 'franco.delivery', // This is a translation string that will be picked up by Vue
                'short_name' => __('Franco'),
                'date' => $date->format('j F'),
                'date_ymd' => $date->format('Y-m-d'),
                'cost' => 0,
            ];

        }


        if($options['before_noon']){
            $date = $this->calculateDeliveryDate(1);
        }
        if($options['after_noon']){
            $date = $this->calculateDeliveryDate(2);
        }

        // Option B
        $deliveryOptions[] = [
            'name' => 'Levering binnen 1 dag', // This is a translation string that will be picked up by Vue
            'short_name' => __('1 dag'), // This is a translation string that will be picked up by Vue
            'date' => $date->format('j F'),
            'cost' => 95,
        ];

        if($options['before_noon']){
            $date = $this->calculateDeliveryDate(0);
        }
        if($options['after_noon']){
            $date = $this->calculateDeliveryDate(1);
        }

        // Option C
        $deliveryOptions[] = [
            'name' => 'Levering binnen 6 uur', // This is a translation string that will be picked up by Vue
            'short_name' => __('6 uur'),
            'date' => $date->format('j F'),
            'date_ymd' => $date->format('Y-m-d'),
            'cost' => 135,
        ];

        if( $options['bezorgregeling'] ){ // Option D
            $deliveryOptions[] = [
                'name' => 'bezorgregeling.delivery', // This is a translation string that will be picked up by Vue
                'date' => '',
                'cost' => 0,
            ];
        }

        return $deliveryOptions;
    }

    /**
     * @param array $product_ids
     */
    public function pickupOptions($product_ids = [], $total_quantity = 1)
    {
        if( empty(Auth::user()->odoo_user_id) ){
            throw ValidationException::withMessages(['Your user doesn\'t have a company connected. Please contact an admin.']);
        }

        setlocale(LC_TIME, 'nl_NL');
        Carbon::setLocale('nl_NL');

        $product_data = (new ProductVariant())->all();
        $products = $product_data->whereIn('id', $product_ids);

        $longest_time = $products->pluck('produce_delay')->max();

        $options = $this->determineOptions($total_quantity);

        $dates = [];

        if ($options['franco']) { // Option A

            if ($options['before_noon']) {
                $date = $this->calculateDeliveryDate(2);
            }

            if ($options['after_noon']) {
                $date = $this->calculateDeliveryDate(3);
            }

            $dates[] = $date;

        }

        if($options['before_noon']){
            $date = $this->calculateDeliveryDate(1);
        }
        if($options['after_noon']){
            $date = $this->calculateDeliveryDate(2);
        }

        $dates[] = $date;

        $dates = collect($dates);

        $fastest_date = $dates->min();

        $pickup_options = [];

        for( $i=0;$i<5;$i++ ){ // We may pick dates a week in the future ( 1 week = 5 business days )
            $pickup_options[] = [
                'label'=> '<p class="futura-bold text-base">' . $this->calculateDeliveryDate($i, $fastest_date->toDateString())->format("D") . '</p><p class="text-sm">' . $this->calculateDeliveryDate($i, $fastest_date->toDateString())->format("j M") . '</p>',
            'date'=>$this->calculateDeliveryDate($i, $fastest_date->toDateString())->format('d-m-Y'),
                'label_full'=>$this->calculateDeliveryDate($i, $fastest_date->toDateString())->format('l j F')
                ];
        }

        return $pickup_options;
    }

}
