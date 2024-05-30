<?php

namespace App\Services;

use App\Enums\CoffinSize;
use App\Enums\Interior;
use App\Enums\Languages;
use App\Models\Odoo\Attribute;
use App\Models\Odoo\Product;
use App\Models\Odoo\ProductVariant;
use App\Models\Odoo\Translation;
use Illuminate\Support\Facades\Cache;

class ProductService
{

    function products()
    {
        $products = (new Product())->get();
        $products = $products
            ->where('type', 'product');
            // ->where('categ_id.0', '=', 58);
        /** @TODO: Extract to Enum "Eindproduct / Toebehoren" */
        // ->where('x_studio_verkocht_in_portaal', false ); // @TODO: Only load items that are sellable in the portal*/

        // #TODO: Move this to model.
        $translations = (new Translation())->productTemplate('name', $products->pluck('id')->toArray());
        $translations = $translations->where('lang', Languages::NL)->groupBy('res_id')->map(function ($translation) {
            return $translation->pluck('value', 'name');
        });

        $variants = (new ProductVariant())->get();
        $variants = $variants
            ->whereIn('product_tmpl_id.0', $products->pluck('id')->toArray());
        $variants = $variants->groupBy('product_tmpl_id.0');

        $products->transform(function ($product) use ($translations) {
            $product['translations'] = $translations[$product['id']];
            return $product;
        });

        $products->transform(function ($product) use ($variants) {
            $variant = $variants[$product['id']];
            $product['variants'] = $variant;
            return $product;
        });

        // Add possible interiors
        // Add possible measurements

        return $products->values();
    }

    public function attributePerVariant($variants)
    {
        $attributes_by_variant_id = [];

        $variant_attributes = (new ProductService())->attributesByVariants($variants);

        foreach ($variant_attributes as $item) {
            $variant = $item['variant'];
            $attributes = collect($item['attributes']);

            if (!$attributes->isEmpty()) {

                $args = [];
                $args['size'] = CoffinSize::getSizeByAttributes($attributes);

                if ($attributes->where('attribute_id.0', \App\Enums\Attribute::INTERIOR)->isNotEmpty()) {
                    $args['interior'] = preg_replace('/[^0-9.]+/', '', $attributes->where('attribute_id.0', \App\Enums\Attribute::INTERIOR)->first()['display_name']);
                }

                $args['attribute']['width'] = \App\Enums\Attribute::getWidth($attributes);
                $args['attribute']['length'] = \App\Enums\Attribute::getLength($attributes);
                $args['attribute']['interior'] = \App\Enums\Attribute::getInterior($attributes);

                $attributes_by_variant_id[$variant['id']] = $args;
            }
        }

        return $attributes_by_variant_id;
    }

    public function attributesByVariants($variants)
    {
        $product_variants = (new ProductVariant())->all();
        $product_variants = $product_variants->whereIn('id', $variants);

        $attributes_per_variant_id = [];

        $attributes = (new Attribute())->all();

        foreach ($product_variants as $product_variant) {
            $attribute_ids = $product_variant['product_template_attribute_value_ids'];
            $variant_attributes = $attributes->whereIn('id', $attribute_ids);

            // Filter attributes based on CoffinSizes & Interior
            $variant_attributes = $variant_attributes->filter(function ($variant_attribute) {

                if (in_array($variant_attribute['product_attribute_value_id'][0], CoffinSize::getIds())) {
                    return true;
                }
                if (in_array($variant_attribute['product_attribute_value_id'][0], Interior::getIds())) {
                    return true;
                }
            });

            if (count($variant_attributes) >= 2) {
                $attributes_per_variant_id[] = [
                    'variant' => $product_variant,
                    'attributes' => $variant_attributes->values()
                ];
            }
        }

        return $attributes_per_variant_id;
    }

    function attributesOf($product_id)
    {
        $product = (new ProductVariant())->find($product_id);
        $attributes = (new Attribute())->all();

        $product['size'] = CoffinSize::getSizeByAttributes($attributes);
        $product['width'] = \App\Enums\Attribute::getWidth($attributes);
        $product['length'] = \App\Enums\Attribute::getLength($attributes);
        $product['interior'] = \App\Enums\Attribute::getInterior($attributes);

        $price = Cache::remember('prdcts-temp_', 360, function () {
            return (new Product())->all();
        });

        $productPriceData = $this->findProduct($price, $product_id);
        $product['_price'] = !empty($productPriceData) ? $productPriceData['list_price'] : 0;
        return $product;
    }
    function attributesOfOptimized($product_id)
    {
        $attributes = (new Attribute())->all();
        $product = [];
        $product['size'] = CoffinSize::getSizeByAttributes($attributes);
        $product['width'] = \App\Enums\Attribute::getWidth($attributes);
        $product['length'] = \App\Enums\Attribute::getLength($attributes);
        $product['interior'] = \App\Enums\Attribute::getInterior($attributes);
        return $product;
    }
    public function findProduct($products, $id)
    {
        foreach ($products as $key => $product) {
            if (is_array($product['product_variant_ids'])) {
                if (in_array($id, $product['product_variant_ids'])) {
                    return $product;
                }
            } else {
                if ($product['product_variant_ids'] == $id) {
                    return $product;
                }
            }
        }
        return false;
    }

    /**
     * @param $product_id
     * @return array
     */
    function attributes($product_id)
    {

        $products = (new Product())->get();
        $product = $products->where('id', $product_id)->first();

        $variants = $product['product_variant_ids'];

        return $this->uniqueVariants($variants);
    }

    /**
     * @param array $variants
     * @return array
     */
    public function getProductVariant($variants)
    {
        return $this->uniqueVariants($variants);
    }

    function uniqueVariants($variants = [])
    {
        $attributes_per_variant_id = collect($this->attributesByVariants($variants));
        $unique_attributes = $attributes_per_variant_id->pluck('attributes')->collapse()->groupBy('attribute_id.0')->map(function ($attributes) {
            return $attributes->unique()->values();
        });

        $variants = collect($attributes_per_variant_id)->pluck('variant');

        $args = [
            'attributes' => $unique_attributes,
            'variants' => $variants,
            /** @TODO: Pricelist id, which one is default? And set pricelist based on contact */
            'prices' => (new PricelistService())->getItemsByPricelist(config('app.pricelist_id'))->whereIn('product_id.0', $variants->pluck('id'))->keyBy('product_id.0'),
        ];

        if (auth('api')->user()->getPricelist()) {
            // Loading alternative prices based upon role and dedicated pricelist
            $args['alternative_prices'] = (new PricelistService())->getAlternativePricingByPricelist(auth('api')->user()->getPricelist()[0]);

            $args['prices'] = (new AlternativePricelistService())->apply($args['prices'], $args['alternative_prices'], $variants);
        }

        return $args;
    }
}
