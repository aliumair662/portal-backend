<?php

namespace App\Models;

use App\Models\Request as VanWijkRequest;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Deregister extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['product_data_array', 'product_attributes', 'selected_undertaker'];
    /*protected $casts = [
        'product_data' => 'array',
    ];*/
    protected $fillable = [
        'user_id',
        'undertaker',
        'undertaker_id',
        'quantity',
        'product_id',
        'product_data',
        'deceased',
        'reason',
        'lot_id',
        'file_number',
        'created_at',
        'updated_at',
        'order_id',
        'order_line_id',
        'picking_line_id',
        'status'
    ];

    public function request(): MorphOne
    {
        return $this->morphOne(VanWijkRequest::class, 'requestable');
    }

    public function getProductAttributesAttribute(){
        $product_data = $this->getProductDataArrayAttribute();
        return (new ProductService())->attributesOf($product_data['product_id'][0]);
    }
    public function getProductDataArrayAttribute(){
        return json_decode($this->attributes['product_data'], true);
    }

    public function client(){
        return $this->hasOne(Client::class, 'id', 'undertaker_id');
    }

    public function getSelectedUndertakerAttribute(){
        return $this->client;
    }

}
