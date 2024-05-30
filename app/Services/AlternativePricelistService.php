<?php

namespace App\Services;

use App\Models\Odoo\Attributes\Interior;
use App\Models\Odoo\Pricelist;
use App\Models\Odoo\PricelistItem;
use App\Models\Odoo\Translation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\Fluent\Concerns\Has;
use Mockery\Generator\StringManipulation\Pass\Pass;

class AlternativePricelistService
{
    public function apply($prices, $alternative_prices, $variants)
    {

        foreach ($alternative_prices as $alternative_price) {

            if ($alternative_price['applied_on'] == '1_product') {
                $prices = $this->applyPerProduct($prices, $alternative_price, $variants);
            }

            if ($alternative_price['applied_on'] == '0_product_variant') {
                $prices = $this->applyPerProductVariant($prices);
            }

            if ($alternative_price['applied_on'] == '2_product_category') {
                $prices = $this->applyPerProductCategory($prices);
            }

            if ($alternative_price['applied_on'] == '3_global') {
                $prices = $this->applyGlobally($prices, $alternative_price);
            }

        }

        return $prices;
    }

    public function applyPerProduct($prices, $alternative_price, $variants)
    {
        $template_ids = $variants->groupBy('product_tmpl_id.0')->keys()->toArray();

        return $prices->transform(function($price) use($alternative_price, $template_ids) {
            if( in_array($alternative_price['product_tmpl_id'][0], $template_ids) ) {
                $price['fixed_price'] += $alternative_price['price_surcharge'];
            }
            return $price;
        });
    }

    public function applyPerProductVariant($prices)
    {
        return $prices;
    }

    public function applyPerProductCategory($prices)
    {
        return $prices;
    }

    public function applyGlobally($prices, $alternative_price)
    {
        return $prices->transform(function($price) use($alternative_price) {
            $price['fixed_price'] += $alternative_price['price_surcharge'];
           return $price;
        });
    }

}
