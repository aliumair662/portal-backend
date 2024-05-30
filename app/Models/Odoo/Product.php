<?php

namespace App\Models\Odoo;

class Product extends Base
{
    public $resource = 'product.template';
    public $fields = [
        'id',
        'name',
        'type',
        'categ_id',
        'is_product_variant',
        'sale_ok',
        'product_variant_ids',
        'qty_available',
        'taxes_id',
        'price',
        'list_price',
        'x_studio_article_name_spreadsheets',
        'x_studio_kist_lijn',
        'x_studio_verkocht_in_portaal',
        'description'
    ];

    public function __construct($data=null)
    {
        $this->data = $data;
    }

    /** @TODO: Isn't cached yet */
    public function all(){
        return $this->connect()->where('type', '=', 'product')->fields($this->fields)->get($this->resource);
    }

    public function delivery(){
        return $this->connect()->where('type', '=', 'service')->where('categ_id', 103)->fields($this->fields)->get($this->resource);
    }

    public function pickup(){
        return $this->connect()->where('type', '=', 'service')->fields($this->fields)->get($this->resource);
    }

    public function collect(){
        $data = collect( $this->data );
        $data = $data->map(function ($item){
            $item['street_number'] = ($item['street_number']==false) ? '' : $item['street_number'];
            return $item;
        });
        return $data;
    }
    public function getById($id, $clearCache = false){
        $this->data = $this->connect()->where('type', '=', 'product')->where('product_variant_ids','=',$id)->fields($this->fields)->get($this->resource);
        return $this;
    }
    public function getByIdCustom($id, $clearCache = false){
        $this->data = $this->connect()->where('type', '=', 'product')->where('id','=',$id)->fields($this->fields)->get($this->resource);
        return $this;
    }
    public function getByProductVariantId($id){
        return $this->connect()->where('type', '=', 'product')->where('product_variant_ids', '=', $id)->fields(['id', 'name'])->get($this->resource);
    }

    public function getByProductVariantIds($ids){
        return $this->connect()->where('type', '=', 'product')->whereIn('product_variant_ids', $ids)->fields(['id', 'name','product_variant_ids'])->get($this->resource);
    }
}
