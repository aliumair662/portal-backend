<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;

class postCreateClientController extends Controller
{
    public function index(){
        $client = (new ClientService())->create( request() );
    }
}
