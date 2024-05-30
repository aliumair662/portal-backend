<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;

class getProductAttributesController extends Controller
{
    public function index($product_id){
        return (new ProductService())->attributes($product_id);
    }
}
