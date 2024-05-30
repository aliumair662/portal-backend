<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;

class getClientRequestsController extends Controller
{
    public function index(){
        return (new ClientService())->requests();
    }
}
