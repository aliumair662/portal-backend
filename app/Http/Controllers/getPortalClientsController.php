<?php

namespace App\Http\Controllers;

use App\Models\Client;

class getPortalClientsController extends Controller
{
    public function index()
    {
        if (auth()->user()->isAdmin()) {
            return Client::whereNotNull('odoo_id')->get();
        } else {
            if (auth()->user()->parent_user_id) {
                return Client::where('user_id', auth()->user()->parent_user_id)->whereNotNull('odoo_id')->get();
            }

            return Client::where('user_id', auth()->user()->id)->whereNotNull('odoo_id')->get();
        }
    }

    public function portalClientsMobile()
    {
        return $this->clientsWithLimitedFields()->transform(function ($data, $key){
            return [
                'id' => $data->id,
                'name' => $data->name,
            ];
        });
    }
    public function clientsWithLimitedFields()
    {
        if (auth()->user()->isAdmin()) {
            return Client::whereNotNull('odoo_id')->select('id', 'name')->get();
        } else {
            if (auth()->user()->parent_user_id) {
                return Client::where('user_id', auth()->user()->parent_user_id)->whereNotNull('odoo_id')->select('id', 'name')->get();
            }

            return Client::where('user_id', auth()->user()->id)->whereNotNull('odoo_id')->select('id', 'name')->get();
        }
    }
}
