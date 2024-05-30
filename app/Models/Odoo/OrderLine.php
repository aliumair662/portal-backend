<?php

namespace App\Models\Odoo;

class OrderLine extends Base
{
    public $resource = 'sale.order.line';
    public $fields = [
        'order_id',
        'route_id',
        'price_subtotal',
        'price_tax',
        'price_total',
        'price_reduce',
        'tax_id',
        'product_id',
        'product_template_id',
        'product_uom_qty',
        'product_uom',
        'product_uom_category_id',
        'qty_delivered',
        'qty_to_invoice',
        'qty_invoiced',
    ];

    public function __construct($data=null)
    {
        $this->data = $data;
    }

    public function all(){
        return $this->cache('locations', function(){
            return $this->connect()->where('usage', 'internal')->fields($this->fields)->get($this->resource);
        });
    }

    public function getById($id, $clearCache = false){
        $this->data = $this->connect()->where('id','=',$id)->fields($this->fields)->get($this->resource);
        return $this;
    }


}
