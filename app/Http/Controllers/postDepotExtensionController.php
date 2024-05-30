<?php

namespace App\Http\Controllers;

use App\Services\RequestService;
use Illuminate\Http\Request;

class postDepotExtensionController extends Controller
{
    public function index(){
        return (new RequestService())->DepotExtension([]);
    }
    public function confirmOrReject(){
        return (new RequestService())->confirmOrReject([]);
    }
}
