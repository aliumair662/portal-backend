<?php

namespace App\Enums;

class CoffinSize
{
    private static $sizes = [
        [
            'width' => 41, // 56cm
            'length' => 38, // 195cm
            'value' => [
                'key' => 'S',
                'label' => 'Standaard'
            ]
        ],
        [
            'width' => 42, // 63cm
            'length' => 38, // 195cm,
            'value' => [
                'key' => 'B',
                'label' => 'Verbreed'
            ]
        ],
        [
            'width' => 43, // 70cm
            'length' => 38, // 195cm
            'value' => [
                'key' => '2B',
                'label' => '2x Verbreed'
            ]
        ],
        [
            'width' => 41, // 56cm
            'length' => 39, // 210cm
            'value' => [
                'key' => 'V',
                'label' => 'Verlengd'
            ]
        ],
        [
            'width' => 42, // 63cm
            'length' => 39, // 210cm
            'value' => [
                'key' => 'VB',
                'label' => 'Verlengd & Verbreed'
            ]
        ]
    ];

    public static function getSizes(): array
    {
        $attributes = (new \App\Models\Odoo\Attribute())->all();

        $sizes = self::$sizes;

        foreach( $sizes as $key => $size ){
            $sizes[$key]['width_label'] = $attributes->where('id', $size['width'])->first();
            $sizes[$key]['length_label'] = $attributes->where('id', $size['length'])->first();
        }

        return $sizes;
    }

    public static function getIds(): array
    {
        $sizes = collect( self::$sizes );
        return $sizes->pluck('width')->merge( $sizes->pluck('length') )->unique()->toArray();
    }

    public static function getSizeByAttributes($attributes){
        foreach( self::getSizes() as $size ){

            $width = null;
            $length = null;

            foreach( $attributes as $attribute ){
                if($attribute['product_attribute_value_id'][0] == $size['width']){
                    $width = true;
                }
                if($attribute['product_attribute_value_id'][0] == $size['length']){
                    $length = true;
                }
            }

            if( $width && $length ){
                return $size['value'];
            }
        }
    }

}
