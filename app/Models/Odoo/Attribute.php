<?php

namespace App\Models\Odoo;

use App\Models\Odoo\Attributes\Length;
use App\Models\Odoo\Attributes\Width;

class Attribute extends Base
{
    public $resource = 'product.template.attribute.value';
    public $fields = [
        'id',
        'name',
        'display_name',
        'product_attribute_value_id',
        'attribute_id',
        'ptav_product_variant_ids',
    ];

    public function all(){
        return $this->get();
    }

    public function __construct($data=null)
    {
        $this->data = $data;
    }
}
