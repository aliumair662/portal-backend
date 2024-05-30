<?php

namespace App\Models\Odoo\Attributes;

use App\Models\Odoo\Attribute;

class Width extends Attribute
{
    function __construct($data = null)
    {
        parent::__construct($data);
    }

    public function all(){
        return $this->cache('attributes-width', function() {
            return $this->connect()->where('attribute_id', 8)->fields($this->fields)->get($this->resource);
        });
    }
}
