<?php

namespace App\Services;

use App\Enums\RequestType;
use App\Models\Client;
use App\Models\Odoo\Location;
use App\Models\Odoo\Partner;
use App\Models\Odoo\Stock;
use App\Models\Request as VanWijkRequest;
use App\Models\User;
use App\Http\Controllers\Odoo\CompanyController;

class ClientService {

    public function create(){
        request()->validate([
            'id' => 'required',
            'user_id' => 'required',
        ]);

        $partners = (new Partner())->get();
        $partner = $partners->where('id', request()->input('id'));

        if( $partner->isEmpty() ){
            $name = ' ';
        } else {
            $name = $partner->first()['display_name'];
        }

        $result = Client::create([
            'name' => $name,
            'user_id' => request()->input('user_id'),
            'odoo_id' => request()->input('id'),
        ]);
        (new CompanyController)->renderCompanyDetail(request()->input('user_id'));
        return $result;
    }

    public function createRequest(){
        request()->validate([
            'user_id' => 'required',
            'name' => 'required',
            'website' => 'required',
            'city' => 'required',
            'zip' => 'required',
        ]);

        $args = [
            'name' => request()->input('name'),
            'user_id' => request()->input('user_id'),
            'website' => request()->input('website'),
            'city' => request()->input('city'),
            'zip' => request()->input('zip'),
        ];

        if( request()->input('company_id') ){
            if( request()->input('user_id') != request()->input('company_id') ){
                $args['user_id'] = request()->input('company_id');
            }
        }

        $client = Client::create($args);

        $request = $client->request()->create([
            'status' => RequestType::OPEN,
            'requested_by' => auth('api')->user()->id,
        ]);
        (new CompanyController)->renderCompanyDetail($args['user_id']);
        $request->client = $client;
        return $request;
    }
    public function createRequestWithUnderTakerName($payload){
        $args = [
            'name' => $payload['name'],
            'user_id' => $payload['user_id'],
            'website' => 'example.com',
            'city' => 'New York',
            'zip' => '10001',
        ];
        $client = Client::create($args);
        $request = $client->request()->create([
            'status' => RequestType::OPEN,
            'requested_by' => auth('api')->user()->id,
        ]);
        (new CompanyController)->renderCompanyDetail($args['user_id']);
        return $client;
    }

    public function delete(){
        request()->validate([
            'odoo_id' => 'required',
            'user_id' => 'required',
        ]);

        if( auth('api')->user()->id == request()->input('user_id') || auth('api')->user()->isAdmin() ){
            Client::where('user_id', request()->input('user_id'))->where('odoo_id', request()->input('odoo_id'))->delete();
            (new CompanyController)->renderCompanyDetail(request()->input('user_id'));
            return true;
        }
    }

    public function decline(){
        request()->validate([
            'id' => 'required',
        ]);

        if( auth('api')->user()->isAdmin() ){
            $request = VanWijkRequest::findOrFail(request()->input('id'));
            $request->status = RequestType::REFUSED;

            return $request->save();
        }
    }

    public function accept(){
        request()->validate([
            'id' => 'required',
            'odoo_id' => 'required',
        ]);

        if( auth('api')->user()->isAdmin() ){
            $request = VanWijkRequest::findOrFail(request()->input('id'));
            $request->status = RequestType::CLOSED;
            $request->save();

            $request->requestable->odoo_id = request()->input('odoo_id');
            return $request->requestable->save();
        }
    }

    public function requests(){
        return VanWijkRequest::where('requestable_type', 'App\Models\Client')->whereIn('status',[ RequestType::OPEN ])->get();
    }
    public function getCustomer($id){
        $partners = (new Partner())->get();
        return $partner = $partners->where('id', $id)->first() ?? [];
        // return Partner::find($id);
    }

}
