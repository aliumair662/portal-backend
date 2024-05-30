<?php

namespace App\Models\Odoo;

class Tax extends Base
{
    public $resource = 'account.tax';
    public $fields = [
        'id',
        'tax_group_id',
        'amount',
        'description',
        // 'product_id',
        'type_tax_use'
    ];

    public function __construct($data=null)
    {
        $this->data = $data;
    }

    public function all(){
        return $this->connect()->fields($this->fields)->get($this->resource);
        // return $this->cache('tax', function(){
        //     return $this->connect()->fields($this->fields)->get($this->resource);
        // });
    }
    public function productTax($productId){
        // return $this->cache('tax-sales', function(){
            return $this->connect()->fields($this->fields)->where('type_tax_use', 'sale')->search($this->resource);
        // });
        // return $this->connect()
        // // ->fields($this->fields)
        // ->where('type_tax_use', 'sale') // Specify the tax type (e.g., sale or purchase)
        // // ->where('product_id', '=', $productId)
        // ->search($this->resource);
    }
}
