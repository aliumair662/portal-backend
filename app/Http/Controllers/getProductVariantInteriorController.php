<?php

namespace App\Http\Controllers;

use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class getProductVariantInteriorController extends Controller
{
    public function index(){
        return (new ProductVariantService())->interior();
    }
}
