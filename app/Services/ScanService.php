<?php

namespace App\Services;

use App\Models\Odoo\Location;
use App\Models\Odoo\Stock;
use App\Services\StockService;
use http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\{Deregister, Log};
use App\Models\Odoo\Product;
use App\Models\Odoo\Translation;
use App\Enums\Languages;
use App\Models\Odoo\Attribute;
use App\Models\Odoo\ProductVariant;

class ScanService
{

    public $trusted_codes = [
        '20221090915654',
        '20221090915650',
        '20211090105654',
        '20211080105654',
    ];

    public $deregistered_codes = [
        '20221090915620',
        '20221090915621',
        '20211090105622',
        '20211080105623',
    ];

    public function __construct()
    {
        Cache::remember('codes_used', 60 * 15, function () {
            return collect([]);
        });
    }

    public function deregister()
    {

        request()->validate([
            'code' => 'required'
        ]);
        $location = $this->getlocation()->first();
        if (!empty($location)) {
            $stock = (new StockService())->getStockByLotId(request()->input('code'), $location['id']);
            $lotCounts = count($stock);
            if ($lotCounts > 0) {
                $price = new Product();
                $productData = $price->getById($stock[0]['product_id'][0])->data;
                $translations = (new Translation())->productTemplate('name', [$productData[0]['id']]);
                $translations = $translations->where('lang', Languages::NL)->groupBy('res_id')->map(function ($translation) {
                    return $translation->pluck('value', 'name');
                });
                $variant = (new ProductVariant())->find($stock[0]['product_id'][0]);
                $attributes = (new Attribute())->all();
                $attributes = $attributes->whereIn('id',$variant['product_template_attribute_value_ids'])->values();
                $data = [
                    'lot_id' => request()->input('code'),
                    'name' => $translations[$productData[0]['id']]['product.template,name'] ?? $productData[0]['name'],
                    'description' => $productData[0]['description'],
                    'length' => count($attributes) > 0 ? $attributes[0]['display_name'] : '',
                    'width' => count($attributes) > 0 ? $attributes[1]['display_name'] : '',
                    'image' => url('storage/products/' . $productData[0]['id'] . '.png'),
                ];
                return response()->json($data);
            } else {
                return response()->json(['error' => 'Couldn\'t find this id'], 404);
            }
        } else {
            return response()->json(['error' => 'Couldn\'t find location for  this id'], 404);
        }
    }

    public function deregisterComplete()
    {
        return (new RequestService())->deregister();
    }

    public function deregisterCancel()
    {
        request()->validate([
            'code' => 'required'
        ]);

        if (in_array(request()->input('code'), $this->deregistered_codes)) {

            $data = [
                'lot_id' => request()->input('code'),
                'name' => 'Eminent Klassiek Eiken verhoogd - 356',
                'description' => 'Simplessa - 31 Ongebleekt katoen zonder koord',
                'length' => '210 cm (1x verlengd)',
                'width' => '63 cm (1x verbreed)',
            ];

            return response()->json($data);
        } else {
            return response()->json(['error' => 'Couldn\'t find this id'], 404);
        }
    }

    public function deregisterCancelComplete()
    {

        $validator = Validator::make(request()->all(), [
            'code' => 'required',
            'reason' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray(),
            ], 400);
        }

        if (in_array(request()->input('code'), Cache::get('codes_used')->toArray())) {
            $data = ['error' => 'This id has ben cancelled already'];
            return response()->json($data, 405);
        }

        if (in_array(request()->input('code'), $this->deregistered_codes)) {

            /* We'll trigger deregister service here */

            $data = ['success' => request()->input('code') . ' has ben cancelled successfully.'];

            Cache::put('codes_used', Cache::get('codes_used')->push(request()->input('code')));

            return response()->json($data);
        } else {
            return response()->json(['error' => 'Couldn\'t find this id'], 404);
        }
    }

    public function return()
    {

        request()->validate([
            'code' => 'required'
        ]);

        $stock = (new StockService())->getStockByLotIdOnly(request()->input('code'));
        $lotCounts = count($stock);
        if ($lotCounts === 0) {
            return response()->json(['error' => 'Couldn\'t find this id'], 404);
        }

        $record = $stock[$lotCounts - 1];
        if (empty($record['location_id'])) {
            return response()->json(['error' => 'No location found!'], 404);
        }

        $locationId = $record['location_id'][0];
        $user_locations = auth()->user()->locations->pluck('odoo_location_id')->toArray();
        if (in_array($locationId, $user_locations)) {
            $price = new Product();
            $productData = $price->getById($record['product_id'][0])->data;
            $product_attributes = (new ProductService())->attributesOf($record['product_id'][0]);
            $data = [
                'lot_id' => request()->input('code'),
                'name' => $productData[0]['name'],
                'description' => $productData[0]['description'],
                'length' => $product_attributes['length']['name'],
                'width' => $product_attributes['width']['name'],
                'image' => url('storage/products/' . $productData[0]['id'] . '.png'),
            ];
            return response()->json([
                'message' => 'location matched! can make a retour.',
                'data' => $data
            ], 200);
        } else {
            $log = new Log;
            $log->user_id = auth()->user()->id;
            $log->code_scanned = request()->input('code');
            $log->result_matched = json_encode($stock);
            $log->location_found = json_encode($user_locations);
            $log->save();
            return response()->json(['error' => 'Deze kist is niet geldig'], 404);
        }
    }

    public function returnComplete()
    {
        return (new RequestService())->return();
    }
    public function getlocation()
    {

        $locations = (new StockService())->locations();

        if (auth()->user()->isAdmin()) {
            return $locations;
        }
        if (!auth()->user()->locations) {
            return [];
        }

        $user_locations = auth()->user()->locations->pluck('odoo_location_id')->toArray();

        $locations = $locations->filter(function ($location) use ($user_locations) {
            return in_array($location['id'], $user_locations);
        });
        return $locations;
    }
}
