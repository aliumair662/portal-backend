<?php

namespace App\Http\Controllers;

use App\Services\RequestService;
use Illuminate\Http\Request;

class postDeregisterController extends Controller
{
    public function index(){
        return (new RequestService())->deregister();
    }
}
