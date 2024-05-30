<?php

namespace App\Http\Controllers;

use App\Enums\Languages;
use App\Models\Odoo\Product;
use App\Models\Odoo\ProductVariant;
use App\Models\Odoo\Translation;
use App\Services\ProductService;
use Illuminate\Http\Request;

class getProductsController extends Controller
{
    public function index(){
        return (new ProductService())->products();
    }
}
