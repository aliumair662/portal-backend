<?php

namespace App\Models\Odoo\Attributes;

use App\Models\Odoo\Attribute;

class Length extends Attribute
{
    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    public function all(){
        return $this->cache('attributes-length', function(){
           return $this->connect()
               ->where('attribute_id', 7)
               ->fields($this->fields)
               ->get($this->resource);
        });
    }
}
