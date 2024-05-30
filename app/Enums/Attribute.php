<?php

namespace App\Enums;

use Illuminate\Support\Collection;

class Attribute
{
    const LENGTH = 7;
    const WIDTH = 8;
    const INTERIOR = 9;

    public static function getWidth(Collection  $attributes){
        $collection = $attributes->where('attribute_id.0',self::WIDTH);
        if( $collection->isNotEmpty() ){
            return $collection->first();
        }
        return null;
    }
    public static function getLength($attributes){
        $collection = $attributes->where('attribute_id.0',self::LENGTH);
        if( $collection->isNotEmpty() ){
            return $collection->first();
        }
        return null;
    }
    public static function getInterior($attributes){
        $collection = $attributes->where('attribute_id.0',self::INTERIOR);
        if( $collection->isNotEmpty() ){
            return $collection->first();
        }
        return null;
    }
}
