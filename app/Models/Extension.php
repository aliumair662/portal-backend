<?php

namespace App\Models;

use App\Models\Odoo\ProductVariant;
use App\Services\ProductService;
use Carbon\Carbon;
use App\Enums\RequestType;
use App\Models\Odoo\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Request as VanWijkRequest;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Extension extends Model
{
    use HasFactory;
    public $fillable = [
        'product_id',
        'reason',
        'quantity',
    ];
    protected $appends = ['product_attributes', 'product'];

    public function request(): MorphOne
    {
        return $this->morphOne(VanWijkRequest::class, 'requestable');
    }

    public function getProductAttribute(){
        $product_variant = (new ProductVariant())->find($this->attributes['product_id']);
        $product = (new ProductService())->products()->where('id', $product_variant['product_tmpl_id'][0])->first();
        return $product;
    }

    public function getProductAttributesAttribute(){
        //return (new ProductService())->attributesOf($this->attributes['product_id']);
        return collect((new ProductService())->attributePerVariant($this->attributes['product_id']))->first();
    }

}
