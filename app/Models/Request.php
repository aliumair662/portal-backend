<?php

namespace App\Models;

use App\Enums\RequestType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $with = ['requestable', 'user'];
    protected $appends = ['date', 'time'];

    public function getDateAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d-m-Y');
    }

    public function getTimeAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('H:i');
    }

    public function requestable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function close()
    {
        $this->status = RequestType::CLOSED;
        $this->save();
    }

    public function refuse(){
        $this->status = RequestType::REFUSED;
        $this->save();
    }

    public function accept($address_id){
        $this->status = RequestType::CLOSED;

        /* @TODO: Connect odoo_delivery_address_id */

        if( strpos( $this->requestable_type, 'UserDeliveryAddress' ) ){
            $delivery_address = $this->requestable;
            $delivery_address->odoo_delivery_address_id = $address_id;
            $delivery_address->save();
        }

        $this->save();
    }

}
