<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class getOrderController extends Controller
{
    public function index(Order $order){
        return $order;
    }
}
