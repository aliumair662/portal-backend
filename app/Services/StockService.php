<?php

namespace App\Services;

use App\Models\Odoo\Location;
use App\Models\Odoo\Stock;
class StockService {

    /**
     * Retrieve all stock locations
     */
    function locations(){ /*$location_id=null*/
        $location = (new Location());
        return $location->all();
    }

    /**
     *
     */
    function fromLocation($location_id){

        $stock = (new Stock());
        return $stock->fromLocation($location_id);
    }

    function getStock(){
        $stock = (new Stock());
        return $stock->all();

    }
    function getStockByLotId($lot_id,$location_id){
        $stock = (new Stock());
        return $stock->fromLotId($lot_id,$location_id);

    }
    function getStockByLotIdOnly($lot_id){
        $stock = (new Stock());
        return $stock->fromLotIdOnly($lot_id);

    }


}
