<?php

namespace App\Models\Odoo;

use App\Enums\CoffinSize;
use App\Models\Odoo\Attributes\Interior;
use App\Models\Odoo\Attributes\Length;
use App\Models\Odoo\Attributes\Width;

class ProductVariant extends Base
{
    public $resource = 'product.product';
    public $fields = [
        'id',
        'partner_ref',
        'price',
        'price_extra',
        'product_tmpl_id',
        'categ_id',
        'sale_ok',
        'qty_available',
        'virtual_available',
        'taxes_id',
        //'kist_beschikbaar_vanaf', // Moet nog in ODOO worden geimplementeerd
        'produce_delay', // Voorlopig deze gebruiken om te testen tot als ^ beschikbaar is
        //'price',
        //'price_extra',
        //'image_variant_256',
        'product_template_attribute_value_ids',
        'combination_indices',
        /*'image_256',*/
    ];

    public function __construct($data=null)
    {
        $this->data = $data;
        $this->length = (new Length());
        $this->width = (new Width());
    }

    public function find($product_id){
        $products = $this->all();
        return $products->where('id', $product_id)->first();
    }
    public function getById($id, $clearCache = false){
        $this->data = $this->connect()->where('id','=',$id)->fields($this->fields)->get($this->resource);
        return $this;
    }

    public function all()
    {
        $variants = $this->cache('variants', function(){
            return $this->connect()->fields($this->fields)->get($this->resource);
        });

        return $variants;
    }

    /** Coffin Size Definitions */
    public function getSizeDefinition($product_id){

        if( isset($product_variant_sizes[ $product_id ]) ){
            return $product_variant_sizes[ $product_id ];
        }

        return false;
    }

    public function collect(){
        $data = collect( $this->data );
        $data = $data->map(function ($item){
            $item['street_number'] = ($item['street_number']==false) ? '' : $item['street_number'];
            return $item;
        });
        return $data;
    }
}
