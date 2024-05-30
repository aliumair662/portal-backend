<?php

namespace App\Models;

use App\Models\Request as VanWijkRequest;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;
    public $guarded = ['id'];
    public $appends = ['order_parsed', 'shipping_parsed', 'shipping_type'];
    protected $casts = [
        'order' => 'array',
        'shipping' => 'array',
    ];

    public function getOrderParsedAttribute(){
         return is_array($this->order) ? $this->order : (array) json_decode($this->order);
    }

    public function getShippingParsedAttribute(){
        return is_array($this->shipping) ? $this->shipping : (array) json_decode($this->shipping);
    }

    public function getShippingTypeAttribute(){
        if( $this->getShippingParsedAttribute()['delivery']->location == null ){
            return 'pickup';
        } else {
            return 'delivery';
        }
    }

    public function request(): MorphOne
    {
        return $this->morphOne(VanWijkRequest::class, 'requestable');
    }
}
