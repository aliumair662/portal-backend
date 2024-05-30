<?php

namespace App\Http\Controllers;

use App\Services\RequestService;
use Illuminate\Http\Request;

class postDeregisterDeclineController extends Controller
{
    public function index(){
        return (new RequestService())->deregisterDecline();
    }
}
