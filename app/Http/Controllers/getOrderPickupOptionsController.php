<?php

namespace App\Http\Controllers;

use App\Services\ShippingService;
use Illuminate\Http\Request;

class getOrderPickupOptionsController extends Controller
{
    public function index(){
        request()->validate([
            'product_ids' => 'required',
            'total_quantity' => 'required',
        ]);

        return (new ShippingService())->pickupOptions(request()->input('product_ids'), request()->input('total_quantity'));
    }
}
