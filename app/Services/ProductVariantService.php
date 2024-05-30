<?php

namespace App\Services;

use App\Enums\CoffinSize;
use App\Models\Odoo\Attributes\Interior;
use App\Models\Odoo\ProductVariant;

class ProductVariantService {

    public function interior(){
        $_interior = [];

        $product_variant_interior = (new Interior())->all();
        foreach( $product_variant_interior as $interior){
            foreach( $interior['ptav_product_variant_ids'] as $variant_id ){
                $_interior[$variant_id] = $interior;
            }
        }

        return $_interior;
    }

    public function sizes(){
        $product_variant_sizes = (new \App\Models\Odoo\ProductVariant())->cache('product-variants-sizes', function(){
            $length = (new \App\Models\Odoo\Attributes\Length());
            $width = (new \App\Models\Odoo\Attributes\Width());
            $sizes = [];
            foreach( CoffinSize::getSizes() as $size ){

                $length_variants = null;
                $width_variants = null;

                $length_variants = $length->get()->groupBy('product_attribute_value_id')[ $size['length'] ]->pluck('ptav_product_variant_ids')->collapse();
                $width_variants = $width->get()->groupBy('product_attribute_value_id')[ $size['width'] ]->pluck('ptav_product_variant_ids')->collapse();

                foreach( $length_variants->intersect($width_variants)->unique()->toArray() as $variant_id ){
                    $sizes[$variant_id] = $size;
                }
            }

            return json_encode($sizes);
        });

        return $product_variant_sizes;
    }

    public function getVariants()
    {
        $allowed_variant_ids = (new ProductService())->products()->where('x_studio_verkocht_in_portaal', true)->pluck('product_variant_ids')->flatten();
        $product_variants = (new ProductVariant())->all()->whereIn('id', $allowed_variant_ids);

        return $product_variants;
    }

}
