<?php

namespace App\Http\Controllers\Odoo;

use App\Enums\RequestType;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Odoo\Partner;
use App\Models\User;
use Illuminate\Support\Facades\{Cache, Log};
use Illuminate\Http\Request;
use DB;

class CompanyController extends Controller
{
    public $cacheDuration = 21600;
    /**
     * Return all Companies from Odoo
     * that are active, is a company
     * and doens't have a parent
     *
     * @return \Illuminate\Support\Collection|void
     */
    public function companies()
    {
        // Getting partners (companies) from cache
        $partners = (new Partner)->fetchAll();

        return $partners->where('company_type', 'company')->where('parent_id', false);
    }

    /**
     *
     * @return \Illuminate\Support\Collection|void
     */
    public function addresses()
    {
        // Getting partners (companies) from cache
        $partners = (new Partner)->get(request()->has('fresh'));

        return $partners->where('active', true)
            ->whereIn('type', ['delivery'])
            ->sortBy('name');
    }

    /**
     *
     * @return \Illuminate\Support\Collection|void
     */
    public function clients()
    {
        // Getting partners (companies) from cache
        $partners = (new Partner)->fetchAll();
        return $partners->where('active', true)
            ->where('company_type', 'company')
            ->sortBy('name');
    }

    /**
     * Get Odoo Company data by ID
     * @param $id
     * @return mixed
     */
    public function company($id)
    {

        // Log::info('Company Id------------------------------------------------- :' .$id);

        $userGet = DB::table('users')->select('id','odoo_user_id')->where('id',$id)->first();
        $id = $userGet->odoo_user_id;
        // $company = Cache::remember('company_cache_' . $id.'-'.$userGet->id, $this->cacheDuration, function () use ($id) {
            $user = User::where('odoo_user_id',$id)->first();
            $company = (new Partner)->findWithoutCache($id)->collect()->first();
            
            // Retrieve "linked" delivery addresses
            $delivery_addresses = $user->deliveryAddresses->pluck('odoo_delivery_address_id')->toArray();

            $company_addresses = [];

            if (isset($company['children'])) {
                foreach ($company['children'] as $address) {
                    if (in_array($address['id'], $delivery_addresses)) {
                        $company_addresses[] = $address;
                    }

                    if ($address['type'] == 'invoice') {
                        $company['invoice'] = $address;
                    }
                }

                $company['children'] = $company_addresses;
            }


            $company['accounts'] = User::where('parent_user_id', $user->id)->get()->toArray();

            $clients = Client::with('request')->where('user_id', $user->id)->get();
            if ($clients->isNotEmpty()) {

                $clients = $clients->filter(function ($client) {
                    if (isset($client['request'])) {
                        if ($client['request']['status'] != RequestType::OPEN && $client['request']['status'] != RequestType::CLOSED) {
                            return false;
                        }
                    }
                    return true;
                });

                $company['clients'] = $clients->toArray();
            }
            return $company;
        // });

        // return $company;
    }


    public function renderCompanyDetail($id){
        $user = User::find($id);
        $company = (new Partner)->findWithoutCache($user->odoo_user_id)->collect()->first();
        // Log::info(json_encode($user));
        if ($user) {
            // Retrieve "linked" delivery addresses
            $delivery_addresses = $user->deliveryAddresses->pluck('odoo_delivery_address_id')->toArray();

            $company_addresses = [];

            if (isset($company['children'])) {
                foreach ($company['children'] as $address) {
                    if (in_array($address['id'], $delivery_addresses)) {
                        $company_addresses[] = $address;
                    }

                    if ($address['type'] == 'invoice') {
                        $company['invoice'] = $address;
                    }
                }

                $company['children'] = $company_addresses;
            }
            $company['accounts'] = User::where('parent_user_id', $user->id)->get()->toArray();
            $clients = Client::with('request')->where('user_id', $user->id)->get();
            if ($clients->isNotEmpty()) {
                $clients = $clients->filter(function ($client) {
                    if (isset($client['request'])) {
                        if ($client['request']['status'] != RequestType::OPEN && $client['request']['status'] != RequestType::CLOSED) {
                            return false;
                        }
                    }
                    return true;
                });
                $company['clients'] = $clients->toArray();
            }
            Cache::put('company_cache_' . $user->odoo_user_id.'-'.$user->id, $company, $this->cacheDuration);
        }
    }
 
    /**
     * Update Odoo resource
     * @param $id
     * @param $data
     */
    public function update(Request $request)
    {
        // return $request->all();
        $user = User::find($request->id);
        $data = collect($request->input('data'))->only(Partner::$fillable)->toArray();
        //return $request->input('data');
        //return [ $user->odoo_user_id, $data ];
        if ($request->input('data.invoice')) {
            $invoice = $request->input('data.invoice');
            $invoice['parent_id'] = $invoice['parent_id'][0];
            $invoice['user_id'] = $invoice['user_id'][0];

            (new Partner)->update($invoice['id'], $invoice);
        }
        // return $data;
        $partner = (new Partner)->update($user->odoo_user_id, $data);
        // return $partner = (new Partner)->findWithoutCache($user->odoo_user_id, $data);
        $this->renderCompanyDetail($user->id);
        return $partner;
    }
}
