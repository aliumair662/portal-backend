<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;

class postCreateRequestClientController extends Controller
{
    public function index(){
        return (new ClientService())->createRequest(request());
    }
}
