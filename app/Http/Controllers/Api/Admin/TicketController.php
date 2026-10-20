<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketReplyRequest;
use App\Http\Requests\Admin\TicketStoreRequest;
use App\Models\Admin;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\Teacher;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::query()->whereNull('parent_id');
        if($request->has('status') && $request->status != ''){
            $tickets->where('status', $request->status);
        }
        if($request->has('parent_id') && $request->parent_id != ''){
            $tickets->where('parent_id', $request->parent_id);
        }
        if($request->has('admin_id') && $request->admin_id != ''){
            $tickets->where('admin_id', $request->admin_id);
        }
        if($request->has('sender_type') && $request->sender_type != ''){
            $tickets->where('sender_type', $request->sender_type);
        }
        if($request->has('sender_id') && $request->sender_id != ''){
            $tickets->where('sender_id', $request->sender_id);
        }

        if($request->has('search') && $request->search != ''){
            $tickets->where('title', 'like', '%'.$request->search.'%');
        }
        if($request->has('message')){
            $tickets->where('message', 'like', '%'.$request->message.'%');
        }

        return response([
            'tickets' => $tickets->with('sender')->paginate(10)
        ]);

    }

    public function store(TicketStoreRequest $request)
    {
        $user = Admin::find(auth('admin')->user()->id);

        $ticket = Ticket::create([
            'title' => $request->title,
            'message' => $request->message,
            'sender_type' => $user->getMorphClass(),
            'sender_id' => $user->id,
        ]);

        return response(['message' => __('messages.ticket_created_successfully'), 'ticket' => $ticket]);
    }

    public function reply(TicketReplyRequest $request, Ticket $ticket)
    {
        $user = Admin::find(auth('admin')->user()->id);

        $reply = Ticket::create([
            'message' => $request->message,
            'parent_id' => $ticket->id,
            'sender_type' => $user->getMorphClass(),
            'sender_id' => $user->id,
        ]);

        $ticket->update(['status' => 'answered']);

        return response(['message' => __('messages.ticket_reply_created_successfully'), 'reply' => $reply]);
    }

    public function show(Ticket $ticket)
    {
        return response([
            'ticket' => $ticket->load('replies')
        ]);
    }
    public function close(Ticket $ticket)
    {
        $ticket->update(['status' => 'closed']);
        return response(['message' => __('messages.ticket_closed_successfully')]);
    }
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $ticket->update(['status' => $request->status]);
        return response(['message' => __('messages.ticket_status_updated_successfully')]);
    }
}