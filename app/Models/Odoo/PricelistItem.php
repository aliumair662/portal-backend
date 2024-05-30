<?php

namespace App\Models\Odoo;

use App\Models\Odoo\Attributes\Length;
use App\Models\Odoo\Attributes\Width;

class PricelistItem extends Base
{
    public $resource = 'product.pricelist.item';
    public $fields = [
        'id',
        'name',
        'product_id',
        'product_tmpl_id',
        'min_quantity',
        'currency_id',
        'price_surcharge',
        'compute_price',
        'price_discount',
        'price',
        'fixed_price',
        'company_id',
        'active',
        'applied_on',
        'base',
        'base_pricelist_id',
        'percent_price',
        'price_round'
    ];

    public function all(){
        return $this->cache('pricelist-items', function() {
            return $this->connect()->where('active', true)->fields($this->fields)->get($this->resource);
        }); // , true
    }

    public function byProductId($product_id){
        return $this->cache('pricelist-items-'.$product_id, function() use($product_id) {
            return $this->connect()->where('product_id', $product_id)->where('active', true)->fields($this->fields)->get($this->resource);
        }); //, true
    }

    public function fromPricelistId($id){
        return $this->connect()->where('pricelist_id', $id)->where('active', true)->get($this->resource);
        return $this->cache('price-list-items-' . $id, function() use($id) {
            //->where('fixed_price', '>', 1)
            // Fixed price will be zero for some pricelist items
        } );
    }

    public function __construct($data=null)
    {
        $this->data = $data;
    }
    public function byProductVariantIdCustom($product_id, $priceList){
        return $this->connect()->where('product_id', $product_id)->where('active', true)
        // ->where("applied_on",'=',$priceList)
        ->where("pricelist_id",'=',$priceList)
        // ->where("pricelist_id",'=',1112)
        ->fields($this->fields)
        ->get($this->resource);
    }
    public function byProductIdCustom($product_id, $priceList){
        return $this->connect()->where('product_tmpl_id', $product_id)->where('active', true)
        // ->where("applied_on",'=',$priceList)
        ->where("pricelist_id",'=',$priceList)
        // ->where("pricelist_id",'=',1112)
        ->fields($this->fields)
        ->get($this->resource);
    }
    public function byAllProductCustom($priceList){
        return $this->connect()->where('pricelist_id', $priceList)->where('active', true)
        ->where("applied_on",'=','3_global')
        // ->where("pricelist_id",'=',1112)
        ->fields($this->fields)
        ->get($this->resource);
    }
    public function byProductCategoryIdCustom($priceList,$categ_id){
        return $this->connect()->where('pricelist_id', $priceList)->where('active', true)
        ->where("categ_id",'=',$categ_id)
        ->fields($this->fields)
        ->get($this->resource);
    }
    public function byProductAndCategoryAndVarientAndAllProduct($priceList,$product_id,$categ_id,$variant_id){
        return $this->connect()->where('pricelist_id', $priceList)->where('active', true)
            // ->where(function($qry) use($product_id,$categ_id,$variant_id){
            //     $qry
            // })
            ->where('product_id', $variant_id)
            ->orWhere("categ_id",'=',$categ_id)
            ->orWhere('product_tmpl_id', $product_id)
            ->orWhere("applied_on",'=','3_global')
            ->fields($this->fields)
            ->get($this->resource);
    }
    public function byPriceIdCustom($priceList){
        return $this->connect()
        // ->where('product_id', $product_id)
        ->where('active', true)
        ->where("pricelist_id",'=',$priceList)
        // ->where("pricelist_id",'=',1112)
        ->fields($this->fields)
        ->get($this->resource);
    }
}
