<?php

namespace App\Models\Odoo;

use App\Enums\Languages;

class Translation extends Base
{
    public $resource = 'ir.translation';
    public $fields = [
        'name',
        'res_id',
        'src',
        'lang',
        'value',
        'state',
    ];

    public function __construct($data=null)
    {
        $this->data = $data;
    }

    public function productPricelist($field){
        return $this->connect()
            ->where('name', 'like', 'product.pricelist,' . $field)
            ->where('state', '=', 'translated')
            ->where('lang', '=', Languages::NL)
            ->fields($this->fields)
            ->get($this->resource);
    }

    public function productTemplate($field, $resource_ids=null, $clear=false){

        $class = $this;

        $translations = $this->cache('translation-' . $field . '-' . sha1(json_encode($resource_ids)), function() use($field, $class, $resource_ids) {
            $translations =  $class->connect()
                ->where('name', 'like', 'product.template,' . $field)
                ->where('state', '=', 'translated')
                ->where('type', '=', 'model')
                ->where('lang', '=', Languages::NL)
                ->fields($class->fields);

                if( isset($resource_ids) ){
                    $translations->whereIn('res_id', $resource_ids);
                }

                return $translations->get($class->resource);
        }, $clear);

        /*if( $resource_ids ){
            return $translations->whereIn('res_id', $resource_ids);
        }*/

        return $translations;
    }
}
