<?php

namespace App\Models\Odoo;

use App\Models\Odoo\Attributes\Length;
use App\Models\Odoo\Attributes\Width;

class PickingLine extends Base
{
    public $resource = 'stock.move.line';
    public $fields = [
        'picking_id',
        'move_id',
        'product_id',
        'qty_done',
        'product_uom_qty',
        'location_id',
        'location_dest_id',
        'product_uom_id',
        'state',
        'lot_id'

        // 'product_uom',
    ];

    public function all(){
        return $this->get();
    }

    public function fromLocation($location_id){

        return $this->cache('picking-' . $location_id, function() use($location_id){
            return $this->connect()->where('location_id', '=', intval($location_id))->fields($this->fields)->get($this->resource);
        }, true); // Clear for now

    }

    // Retrieve all picking lines for a given location that
    // not have the state: 'done' or 'cancel'
    // Group by product_id and sum the qty_done
    public function toLocation($location_id){

        return $this->connect()
                ->where('location_dest_id', '=', intval($location_id))
                ->fields($this->fields)->get($this->resource);



        $result = $this->cache('pickingline-tolocation-' . $location_id, function() use($location_id){
            return $this->connect()
                ->where('location_dest_id', '=', intval($location_id))
                ->where('state', '!=','cancel')
                ->where('state', '!=','done')
                ->fields($this->fields)->get($this->resource);
        }, true); // We need to clear the cache, as this should be updated every time

        $stockOnTheMove = [];

        foreach( $result as $stockMove ){
            if( !isset($stockOnTheMove[$stockMove['product_id'][0]]) ){
                $stockOnTheMove[$stockMove['product_id'][0]] = 0;
            }
            $stockOnTheMove[$stockMove['product_id'][0]] += $stockMove['qty_done'];
        }

        return $stockOnTheMove;

    }

    public function getById($id, $clearCache = false){
        $this->data = $this->connect()->where('id','=',$id)->fields($this->fields)->get($this->resource);
        return $this;
    }
    public function getByPickingId($id, $clearCache = false){
        return $this->data = $this->connect()->where('picking_id','=',$id)->fields($this->fields)->get($this->resource);
        // return $this;
    }

    public function __construct($data=null)
    {
        $this->data = $data;
    }
    // public function updateLine($id, $update){
    //     // return $this->data = $this->connect()->where('id',$id)->update($id,$update);
    //     return $this->connect()->where($this->resource, [['id', '=', $id]])
    //      ->update($update);
    // }
}
