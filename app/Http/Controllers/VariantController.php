<?php

namespace App\Http\Controllers;

use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function getVariant(){
        $product_variants = (new ProductVariantService())->getVariants();
        return $product_variants;
    }
}
