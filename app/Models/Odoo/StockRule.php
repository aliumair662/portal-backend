<?php

namespace App\Models\Odoo;

use Illuminate\Support\Str;

class StockRule extends Base
{
    public $resource = 'stock.rule';
    public $fields = [
        'id',
        'route_id',
        'location_src_id',
    ];

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    /** @TODO: Isn't cached yet */
    public function all()
    {
        return $this->connect()->where('type', '=', 'product')->fields($this->fields)->get($this->resource);
    }

    public function forLocation($location_id)
    {

        return $this->cache('stockrule-forlocation-' . $location_id, function () use ($location_id) {
            return $this->connect()->where('location_src_id', '=', intval($location_id))->fields($this->fields)->get($this->resource);
        }, true); // Clear for now

    }

    public function getById($id, $clearCache = false)
    {
        $this->data = $this->connect()->where('id', '=', $id)->fields($this->fields)->get($this->resource);
        return $this;
    }
}