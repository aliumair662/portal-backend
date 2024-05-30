<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;

class getProductVariantController extends Controller
{
    public function index(){
        return (new ProductService())->products();
    }
}
