<?php

namespace App\Http\Controllers;

use App\Services\OrderService;

class postCreateOrderController extends Controller
{
    public function index(){
        return (new OrderService())->create();
    }
}
