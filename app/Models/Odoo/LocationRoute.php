<?php

namespace App\Models\Odoo;

class LocationRoute extends Base
{
    public $resource = 'stock.location.route';
    public $fields = [
        'id',
        'supplied_wh_id',
        'supplier_wh_id',
        'warehouse_ids',
        'deposit_partner_id',
        'sale_selectable',
        'rule_ids',
    ];

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function all()
    {
        // return $this->connect()->where('id','=',49)->get($this->resource);
        // return $this->connect()->where('display_name','=',"Blom en De Ridder Uitvaartverzorging")->get($this->resource);
        // return $this->connect()->where('deposit_partner_id','=',107074)->get($this->resource);
        return $this->cache('locations', function () {
            return $this->connect()->where('usage', 'internal')->fields($this->fields)->get($this->resource);
        });
    }

    public function fromIds($ids)
    {
        return $this->cache('routes', function () use ($ids) {
            return $this->connect()->whereIn('id', $ids)->fields($this->fields)->get($this->resource);
        }, true);
    }

    public function getById($id, $clearCache = false)
    {
        $this->data = $this->connect()->where('id', '=', $id)->fields($this->fields)->get($this->resource);
        return $this;
    }
}