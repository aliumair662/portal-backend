<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Enums\TicketType;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    public function show(Ticket $ticket)
    {
        $ticketId = $ticket->id;
        $ticketCreatedBy = $ticket->user_id;
        $currentUser = Auth::user()->id;
        // when customer views the ticket then all messages are set to be viewed
        // and only be done when customer who created the ticket views the ticket
        if ($ticketCreatedBy == $currentUser) {
            TicketMessage::where('ticket_id', $ticketId)
                ->update(['message_viewed' => true]);
        }

        if (Auth::user()->is_admin || Auth::user()->is_super_admin) {
            Ticket::where('id', $ticketId)
                ->update(['ticket_viewed' => true]);
        }

        // return the current ticket
        return $ticket;
    }

    public function ticket(Request $request)
    {
        try{
            $request->validate([
                'subject' => 'required',
                'message' => 'required',
                'file' => 'array|max:3',
                'file.*' => 'mimes:jpg,jpeg,png,pdf|max:10000'
            ]);
        }catch(Exception $ex){
            // Log::info($ex->getMessage());
        }


        $ticket = Ticket::create([
            'ref' => Str::uuid(),
            'subject' => $request->input('subject'),
            'name_user' => $request->user_full_name ?? auth('api')->user()->first_name . ' ' . auth('api')->user()->last_name,
            'user_id' => $request->user_id ?? auth('api')->user()->id,
            'status' => TicketType::OPEN
        ]);

        $ticketMessage = TicketMessage::create([
            'posted_by' => auth('api')->user()->id,
            'ticket_id' => $ticket->id,
            'message' => $request->input('message')
        ]);

        if ($request->hasfile('file')) {
            foreach ($request->file('file') as $file) {
                $ticketMessage
                    ->addMedia($file)
                    ->toMediaCollection();
            }
        }

        return $ticket->fresh();
    }

    public function message(Request $request, Ticket $ticket)
    {
        $action = request()->input('action');
        if ($action != 'close') {
            $request->validate([
                'message' => 'required',
                'file' => 'array|max:3',
                'file.*' => 'mimes:jpg,jpeg,png,pdf|max:10000'
            ]);
            $message = [
                'posted_by' => auth('api')->user()->id,
                'ticket_id' => $ticket->id,
                'message' => $request->input('message'),
                'status' => $request->input('status')
            ];

            $ticketMessage = TicketMessage::create($message);

            if ($request->hasfile('file')) {
                foreach ($request->file('file') as $file) {
                    $ticketMessage
                        ->addMedia($file)
                        ->toMediaCollection();
                }
            }
        }
        if (request()->input('action')) {
            $action = '';
            switch (request()->input('action')) {
                case 'archive':
                    $action = TicketType::ARCHIVE;
                    break;
                case 'close':
                    $action = TicketType::CLOSED;
                    break;
            }
            $ticket->status = $action;
            $ticket->save();
        }

        // Update on ticket
        $ticket->touch();

        return $ticket->fresh();
    }

    public function status(Request $request, Ticket $ticket)
    {

        $request->validate([
            'action' => 'required',
        ]);

        if (request()->input('action')) {
            $action = '';
            switch (request()->input('action')) {
                case 'archive':
                    $action = TicketType::ARCHIVE;
                    break;
                case 'open':
                    $message = [
                        'posted_by' => auth('api')->user()->id,
                        'ticket_id' => $ticket->id,
                        'message' => $request->input('message'),
                        'is_ticket_reopen' => $request->input('is_ticket_reopen')
                    ];

                    $ticketMessage = TicketMessage::create($message);
                    $action = TicketType::OPEN;
                    break;
                case 'close':
                    $action = TicketType::CLOSED;
                    break;
            }

            $ticket->status = $action;
            $ticket->save();
        }

        return $ticket;
    }

    public function all(Request $request)
    {
        /** @var User $user */
        $user = auth('api')->user();
        $filter = $request->input();
        if ($user->isAdmin()) {
            return Ticket::when($filter, function ($query) use ($filter) {
                $query->where('user_id', $filter['user_id']);
            })
                ->get()
                ->sortByDesc('created_at')
                ->values();
        } else {
            return Ticket::where('user_id', $user->id)->where('status', '!=', 'closed')->get()->values();
        }
    }

    public function companies()
    {
        return User::whereNotNull('odoo_user_id')->get();
    }
}