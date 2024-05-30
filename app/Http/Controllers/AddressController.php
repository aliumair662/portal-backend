<?php

namespace App\Http\Controllers;

use App\Models\UserDeliveryAddress;
use App\Models\Request as VanWijkRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Odoo\CompanyController;

class AddressController extends Controller
{
    public function requests($id){

        $requests = UserDeliveryAddress::with('request')->where('user_id', $id)->where('request.status', 'open')->get();

        return $requests;

    }

    public function add(){
        request()->validate([
            'id' => 'required',
            'user_id' => 'required',
        ]);

        $result = UserDeliveryAddress::create([
            'user_id' => request()->input('user_id'),
            'odoo_delivery_address_id' => request()->input('id'),
        ]);
        // $user = User::findOrFail(request()->input('user_id'));
        (new CompanyController)->renderCompanyDetail(request()->input('user_id'));
        return $result;
    }

    public function delete(){

        request()->validate([
            'id' => 'required',
        ]);

        $id = request()->input('id');

        // @TODO: Safeguard middleware only owner of address, portal user or superadmin
        $result = UserDeliveryAddress::where('id', $id)->delete();

        return $result;
    }
}
