<?php

namespace App\Http\Controllers;

use App\Services\RequestService;
use Illuminate\Http\Request;

class getDepotExtensionController extends Controller
{
    public function index(){
        return (new RequestService())->getDepotExtension();

    }
    public function history(){
        return (new RequestService())->getHistory();
    }
}
