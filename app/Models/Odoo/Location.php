<?php

namespace App\Models\Odoo;

class Location extends Base
{
    public $resource = 'stock.location';
    public $fields = [
        'id',
        'name',
        'complete_name',
        'display_name',
        'active',
        'usage',
        'child_ids',
        'warehouse_id',
        'company_id',
        'x_studio_assortiment',
        // 'x_studio_location_partner_id',
        'partner_id',
        'x_studio_picking_types_with_default_destination_location'
    ];

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function all()
    {
        return $this->cache('locations', function () {
            return $this->connect()->where('usage', 'internal')->fields($this->fields)->get($this->resource);
        });
    }
    public function getByIdCustom($id){
        return $this->connect()->where('usage', 'internal')
        ->where('id','=',$id)
        ->fields($this->fields)
        ->get($this->resource);
    }
}