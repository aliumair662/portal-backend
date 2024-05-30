<?php

namespace App\Services;

use App\Enums\RequestType;
use App\Models\Order;
use App\Models\Request;

class OrderService {

    /**
     * Retrieve all stock locations
     */
    function orders(){ /*$location_id=null*/
        return Order::all();
    }

    /**
     * Create an order
     */
    function create(){
        request()->validate([
            'order' => 'required',
            'shipping' => 'required',
        ]);

        $create = [
            'user_id' => auth('api')->user()->id,
            'order' => json_encode(request()->input('order')),
            'shipping' => json_encode(request()->input('shipping')),
            'note' => request()->input('note'),
        ];

        $order = Order::create($create);

        $request = $order->request()->create([
            'status' => RequestType::OPEN,
            'requested_by' => auth('api')->user()->id,
        ]);

        return $request;


        return $order;
    }

    /**
     * Fetch request data for orders that are 'open'
     */
    function requests(){
        return Request::where('requestable_type', 'App\Models\Order')->orderBy('created_at', 'DESC')->where('status', 'open')->get();
    }

    /**
     * Retrieve specific order
     */
    function show($id){

    }
}
