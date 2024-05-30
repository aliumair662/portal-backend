<?php

namespace App\Models\Odoo;

use App\Models\Odoo\Attributes\Length;
use App\Models\Odoo\Attributes\Width;

class Pricelist extends Base
{
    public $resource = 'product.pricelist';
    public $fields = [
        'id',
        'name',
        'item_ids',
        'company_id'
    ];

    public function all(){
        return $this->cache('pricelists', function() {
            return $this->connect()->where('active', true)->fields($this->fields)->get($this->resource);
        }, true);
    }
    public function getByIds($id){
        return $this->connect()->where('active', true)
        ->where('id','=',$id)
        ->fields($this->fields)
        ->get($this->resource);
    }

    public function __construct($data=null)
    {
        $this->data = $data;
    }
}
