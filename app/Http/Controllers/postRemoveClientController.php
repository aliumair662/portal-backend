<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;

class postRemoveClientController extends Controller
{
    public function index(){
        $client = (new ClientService())->delete(request());
    }
}
