<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class getProductsPricesController extends Controller
{
    public function index(){
        // Pricelist id can change based upon client that is logged in
        $pricelist_id = 73;
        return (new \App\Services\PricelistService())->getItemsByPricelist($pricelist_id);
    }
}
