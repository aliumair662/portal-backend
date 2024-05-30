<?php

namespace App\Models\Odoo;

use App\Models\Odoo\Attributes\Length;
use App\Models\Odoo\Attributes\Width;

class Production extends Base
{
    public $resource = 'mrp.production';
    public $fields = [
        'id',
        'name',
        'product_id',
        'orderpoint_id',
        'production_location_id',
        'location_dest_id',
        'order_line_id',
        'order_line_routing_id',
        'product_qty',
        'state',
        'date_planned_start',
        'date_planned_finished',
        'date_start',
        'date_finished',
        'origin',
        'deceased_name',
        'case_number',
        'company_id',
        'partner_id',
        'x_studio_klant',
        'x_studio_field_BQHnj',
        'bom_id'
    ];

    public function all()
    {
        return $this->get();
    }

    public function fromLocation($location_id)
    {
        $rule_ids = (new \App\Models\Odoo\StockRule())->forLocation($location_id)->groupBy('route_id.0')->keys()->toArray();
        $route_ids = !empty($rule_ids) ? (new \App\Models\Odoo\LocationRoute())->fromIds($rule_ids)->groupBy('id')->keys()->toArray() : [];
        $productions = !empty($route_ids) ? (new \App\Models\Odoo\Production())->fromRouteIds($route_ids)->whereNotIn('state', ['cancel', 'done']) : [];
        $in_production = [];

        foreach ($productions as $production) {
            if (!array_key_exists($production['product_id'][0], $in_production)) {
                $in_production[$production['product_id'][0]] = array(
                    'id' => $production['id'],
                    'product_id' => $production['product_id'],
                    'in_production' => 0
                );
            }
            $in_production[$production['product_id'][0]]['in_production'] += $production['product_qty'];
        }

        return $in_production;
    }

    public function fromRouteIds($ids)
    {
        return $this->cache('production-routeids', function () use ($ids) {
            return $this->connect()->whereIn('x_studio_field_BQHnj', $ids)->fields($this->fields)->get($this->resource);
        }, true);
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