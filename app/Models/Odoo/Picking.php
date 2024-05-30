<?php

namespace App\Models\Odoo;

use App\Models\Odoo\Attributes\Length;
use App\Models\Odoo\Attributes\Width;

class Picking extends Base
{
    public $resource = 'stock.picking';
    public $fields = [
        'id',
        'name',
        'location_id',
        'location_dest_id',
        'move_lines',
        //'move_lines.product_id',
        'move_line_ids',
        'move_line_nosuggest_ids',
        //'move_line_nosuggest_ids.product_qty',
        'product_id',
        'state',
        'company_id',
        'lot_id'
    ];

    public function all()
    {
        return $this->get();
    }

    public function fromLocation($location_id)
    {
        return $this->cache('picking-' . $location_id, function () use ($location_id) {
            return $this->connect()->where('location_id', '=', intval($location_id))->fields($this->fields)->get($this->resource);
        }, true); // Clear for now

    }

    public function getAssignedFromLocation($location_id)
    {
        $pickingType = $this->connect()->where('default_location_dest_id',intval($location_id))->get('stock.picking.type');

        return $this->connect()
            ->where('state', '=', 'assigned')
            ->where('picking_type_id', '=', $pickingType[0]['id'] ?? 0)
            ->get($this->resource);
    }

    public function fromLocationWithFilters($location_id)
    {
        $pickingType = $this->connect()->where('default_location_dest_id',intval($location_id))->get('stock.picking.type');
        return $this->connect()->where('picking_type_id',$pickingType[0]['id'])->get($this->resource);
    }
    public function withLimit($limit, $offset)
    {
        return $this->connect()->fields($this->fields)->limit($limit, $offset)->get($this->resource);
    }
    public function toLocation($location_id)
    {

        return $this->cache('picking-tolocation-' . $location_id, function () use ($location_id) {
            return $this->connect()->where('location_dest_id', '=', intval($location_id))->fields($this->fields)->get($this->resource);
        }, true); // Clear for now

    }

    public function getById($id, $clearCache = false)
    {
        $this->data = $this->connect()->where('id', '=', $id)->fields($this->fields)->get($this->resource);
        return $this;
    }

    public function __construct($data = null)
    {
        $this->data = $data;
    }
}
