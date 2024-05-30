<?php

namespace App\Models\Odoo\Attributes;

use App\Models\Odoo\Attribute;

class Interior extends Attribute
{
    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    public function all(){
        return $this->cache('attributes-interior', function() {
            return $this->connect()->where('attribute_id', 9)->fields($this->fields)->get($this->resource);
        });
    }
}
