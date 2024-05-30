<?php

namespace App\Http\Controllers;

use App\Services\ScanService;
use Illuminate\Http\Request;

class postScanDeregisterCompleteController extends Controller
{
    public function index(){
        return (new ScanService())->deregisterComplete();
    }
}
