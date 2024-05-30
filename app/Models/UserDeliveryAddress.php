<?php

namespace App\Models;

use App\Models\Odoo\Partner;
use App\Models\Request as VanWijkRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class UserDeliveryAddress extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['address'];
    //protected $with = ['requestOpen'];

    public function request(): MorphOne
    {
        return $this->morphOne(VanWijkRequest::class, 'requestable');
    }

    public function requestOpen(): MorphOne
    {
        return $this->morphOne(VanWijkRequest::class, 'requestable')->where('status', '=', 'open');
    }

    public function getAddressAttribute()
    {
        if( $this->attributes['odoo_delivery_address_id'] == null ){
            return [];
        }

        $base = (new \App\Models\Odoo\Base())->connect();
        $partner = (new \App\Models\Odoo\Partner());
        $address = $base->where('id', $this->attributes['odoo_delivery_address_id'])->fields($partner->fields)->get('res.partner');

        if( $address->isNotEmpty() ){
            return $address->first();
        } else {
            return [];
        }
    }
}
