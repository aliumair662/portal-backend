<?php

namespace App\Http\Controllers;

use App\Services\StockService;
use Illuminate\Http\Request;

class getStockLocationsController extends Controller
{
    public function index(){
        $locations = (new StockService())->locations();

        if( auth()->user()->isAdmin() ){
            return $locations;
        }

        if( !auth()->user()->locations ){
            return [];
        }

        $user_locations = auth()->user()->locations->pluck('odoo_location_id')->toArray();

        $locations = $locations->filter(function($location) use($user_locations){
            return in_array($location['id'], $user_locations);
        });

        return $locations;
    }
}
