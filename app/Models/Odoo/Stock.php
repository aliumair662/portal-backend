<?php

namespace App\Models\Odoo;

use Illuminate\Support\Str;

class Stock extends Base
{
    public $resource = 'stock.quant';
    public $fields = [
        'id',
        'product_id',
        'product_tmpl_id',
        'location_id',
        'lot_id',
        'reserved_quantity',
        'available_quantity',
        'quantity',
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

    public function fromLocation($location_id)
    {

        return $this->cache('stock-' . $location_id, function () use ($location_id) {
            return $this->connect()->where('location_id', '=', intval($location_id))->fields($this->fields)->get($this->resource);
        });

    }

    public function collect()
    {
        $data = collect($this->data);
        $data = $data->map(function ($item) {
            $item['street_number'] = ($item['street_number'] == false) ? '' : $item['street_number'];
            return $item;
        });
        return $data;
    }
    public function fromLotId($lot_id,$location_id)
    {

        return $this->cache('stock-' . $lot_id.$location_id, function () use ($lot_id,$location_id) {
            return $this->connect()->where('location_id', '=', $location_id)->where('lot_id', '=', $lot_id)->fields($this->fields)->get($this->resource);
        });

    }
    public function fromLotIdOnly($lot_id)
    {

        return $this->cache('stock-' . $lot_id, function () use ($lot_id) {
            return $this->connect()->where('lot_id', '=', $lot_id)->fields($this->fields)->get($this->resource);
        });

    }


}
