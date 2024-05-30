<?php

namespace App\Models;

use App\Models\Request as VanWijkRequest;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DepotReturn extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $guarded = ['id'];
    protected $table = 'returns';
    protected $with = ['media'];
    protected $appends = ['product_attributes', 'product_data_parsed'];

    public function request(): MorphOne
    {
        return $this->morphOne(VanWijkRequest::class, 'requestable');
    }

    public function getProductAttributesAttribute(){
        return (new ProductService())->attributesOf($this->attributes['product_id']);
    }

    public function getProductDataParsedAttribute(){
        return json_decode($this->product_data, true);
    }

}
