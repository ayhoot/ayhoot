<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\TicketReplyRequest;
use App\Http\Requests\Student\TicketStoreRequest;
use App\Models\Student;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request){
        $tickets=Ticket::query()->where(['sender_id'=>auth('student')->user()->id,'sender_type'=>'App\Models\Student','parent_id'=>null]);
        if($request->status){
            $tickets=$tickets->where('status',$request->status);
        }
        return response([
            'tickets'=>$tickets->paginate(10)
        ]);

    }
    public function store(TicketStoreRequest $request)
    {


        
        $user = Student::find(auth('student')->user()->id);

        $ticket = Ticket::create([
            'title' => $request->title,
            'message' => $request->message,
            'sender_type' => $user->getMorphClass(),
            'sender_id' => $user->id,
        ]);

        return response(['message' => __('messages.ticket_created_successfully'), 'ticket' => $ticket], 201);
    }

    // Reply to a ticket
    public function reply(TicketReplyRequest $request, Ticket $ticket)
    {
        $user = Student::find(auth('student')->user()->id);

        $reply = Ticket::create([
            'message' => $request->message,
            'parent_id' => $ticket->id,
            'sender_type' => $user->getMorphClass(),
            'sender_id' => $user->id,
        ]);

        return response(['message' => __('messages.reply_sent_successfully'), 'ticket' => $reply]);
    }

    // Get Ticket with Replies
    public function show(Ticket $ticket)
    {
        return response([
            'ticket' => $ticket->load('replies')
        ]);
    }


    public function close(Ticket $ticket){
        $ticket->status='closed';
        $ticket->save();
        return response(['message'=>__('messages.ticket_closed_successfully')]);
    }
}
