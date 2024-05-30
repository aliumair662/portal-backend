<?php

namespace App\Http\Controllers;

use App\Services\RequestService;
use Illuminate\Http\Request;

class postReturnController extends Controller
{
    public function index(){
        return (new RequestService())->return();
    }

    public function confirmOrReject(){
        return (new RequestService())->confirmOrReject();
    }
}
