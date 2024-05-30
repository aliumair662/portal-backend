<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class getOrdersController extends Controller
{
    public function index(){
        $orders = Order::where('user_id', auth()->user()->getPortalUserId())->orderBy('created_at', 'DESC')->take(10)->get();

        return $orders;
    }
}
