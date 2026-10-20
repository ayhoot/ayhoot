<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Api\Instructor\Concerns\InteractsWithInstructor;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use InteractsWithInstructor;

    public function index(Request $request)
    {
        $instructor = $this->getInstructor();

        $tickets = Ticket::query()->where([
            'sender_id' => $instructor->id,
            'sender_type' => $instructor->getMorphClass(),
            'parent_id' => null,
        ]);

        if ($request->status) {
            $tickets = $tickets->where('status', $request->status);
        }

        return response([
            'tickets' => $tickets->paginate(10)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $user = $this->getInstructor();

        $ticket = Ticket::create([
            'title' => $request->title,
            'message' => $request->message,
            'sender_type' => $user->getMorphClass(),
            'sender_id' => $user->id,
        ]);

        $ticket->replies()->create([
            'message' => $request->message,
            'sender_type' => $user->getMorphClass(),
            'sender_id' => $user->id,
        ]);

        return response(['message' => __('messages.ticket_created_successfully'), 'ticket' => $ticket], 201);
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $user = $this->getInstructor();

        if ($ticket->sender_id !== $user->id || $ticket->sender_type !== $user->getMorphClass()) {
            return response(['message' => __('messages.unauthorized')], 403);
        }

        $request->validate(['message' => 'required|string']);

        $ticket->replies()->create([
            'message' => $request->message,
            'sender_type' => $user->getMorphClass(),
            'sender_id' => $user->id,
        ]);

        $ticket->update(['status' => 'open']);

        return response(['message' => __('messages.reply_sent_successfully'), 'ticket' => $ticket->load('replies.sender')]);
    }

    public function show(Ticket $ticket)
    {
        $user = $this->getInstructor();

        if ($ticket->sender_id !== $user->id || $ticket->sender_type !== $user->getMorphClass()) {
            return response(['message' => __('messages.unauthorized')], 403);
        }

        return response(['ticket' => $ticket->load('replies.sender')]);
    }

    public function close(Ticket $ticket)
    {
        $user = $this->getInstructor();

        if ($ticket->sender_id !== $user->id || $ticket->sender_type !== $user->getMorphClass()) {
            return response(['message' => __('messages.unauthorized')], 403);
        }

        $ticket->update(['status' => 'closed']);

        return response(['message' => __('messages.ticket_closed_successfully'), 'ticket' => $ticket]);
    }
}
