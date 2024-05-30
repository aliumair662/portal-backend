<?php

namespace App\Http\Controllers;

use App\Enums\OrganisationType;
use App\Enums\RequestType;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ClientService;

class getAllClientsController extends Controller
{
    public function index()
    {


        if (auth()->user()->isAdmin()) {
            $clients = DB::table('users')->select('id')->where('odoo_organisation_type_id', OrganisationType::FUNERAL_DIRECTOR)->pluck('id')->values();

            return User::whereIn('id', $clients)->get();
        } else {

            $clients = Client::with('request')->where('user_id', auth()->user()->id)->get();

            if ($clients->isNotEmpty()) {

                $clients = $clients->filter(function ($client) {
                    if (isset($client['request'])) {
                        if ($client['request']['status'] != RequestType::OPEN && $client['request']['status'] != RequestType::CLOSED) {
                            return false;
                        }
                    }
                    return true;
                });

                return $clients->toArray();
            }
        }
    }
    public function detail($id){
        return (new ClientService)->getCustomer($id);
    }
}