<?php

namespace App\Http\Controllers;

use App\Enums\Attribute;
use App\Enums\CoffinSize;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class getProductAttributesFromVariantsController extends Controller
{
    public function index(){
        request()->validate([
            'variants' => 'required'
        ]);

        return (new ProductService())->attributePerVariant(request()->input('variants'));
    }
}
