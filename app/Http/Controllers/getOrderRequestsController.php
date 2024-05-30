<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;

class getOrderRequestsController extends Controller
{
    public function index(){
        return (new OrderService())->requests();
    }
}
