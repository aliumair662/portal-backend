<?php

namespace App\Services;

use App\Models\Odoo\Attributes\Interior;
use App\Models\Odoo\Pricelist;
use App\Models\Odoo\PricelistItem;
use App\Models\Odoo\Translation;

class PricelistService {

    function getAll(){

        $pricelists = (new Pricelist())->all();
        $translations = (new Translation())->productPricelist('name');

        $pricelists->transform(function($pricelist) use($translations){
            $pricelist['translation'] = $translations->where('res_id', $pricelist['id'])->first();
            return $pricelist;
        });

        return $pricelists;
    }

    function getItemsByPricelist($pricelist_id){
        $pricelist_items = (new PricelistItem())->fromPricelistId($pricelist_id);
        return $pricelist_items;
    }

    function getAlternativePricingByPricelist($pricelist_id){
        $pricelist_items = (new PricelistItem())->fromPricelistId($pricelist_id);
        return $pricelist_items;
    }

}
