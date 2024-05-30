<?php

namespace App\Http\Controllers\Requests;

use App\Models\Ticket;
use App\Enums\RequestType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Request as VanWijkRequest;
use Edujugon\Laradoo\Odoo;
use Edujugon\Laradoo\Facades\Laradoo;


class RequestController extends Controller
{
    /*protected $middleware = [
        ['permission:view requests']
    ];*/

    public function __construct()
    {
        $this->middleware(['can:view requests']);
    }

    public function all()
    {
        // return $depositCount = Laradoo::depositCount(); 
        $requests = VanWijkRequest::where('status', RequestType::OPEN)->get();
        $tickets = Ticket::where('ticket_viewed', 0)->count();
        // Odoo::connect();
        // Establish connection to Odoo
        // $odoo = new Odoo();
        // $odoo->connect();
        // $odoo = (new Odoo())
        //     ->username(config('odoo.username'))
        //     ->password(config('odoo.password'))
        //     ->db(config('odoo.db'))
        //     ->host(config('odoo.host'))
        //     ->connect();
        // dd($odoo);


        // // Retrieve deposit cancellation count
        // return $count = $odoo
        //     ->where('state', '=', 'cancel')
        //     ->where('is_deposit', '=', true)->get('sale.order')
        // ;
        // ->with('sale.order');
        // $odoo = (new Odoo())
        //     ->username(config('odoo.username'))
        //     ->password(config('odoo.password'))
        //     ->db(config('odoo.db'))
        //     ->host(config('odoo.host'))
        //     ->connect();
        // return $depositCancellationCount = $odoo->where('account', 'account.move.line')
        //     ->where('account', [
        //         ['deposit', '>', 0],
        //         ['debit', '=', 0],
        //         ['credit', '>', 0],
        //     ])->count('account.move.line');

        $map = $requests->map(function ($request) {
            return [
                'type' => str_replace('App\\Models\\', '', $request['requestable_type']),
            ];
        })->groupBy('type')->map->count();

        $map['Ticket'] = $tickets;
        return $map;
    }
}