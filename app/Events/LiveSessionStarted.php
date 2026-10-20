<?php

namespace App\Events;

use App\Models\LiveSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;

    public function __construct(LiveSession $session)
    {
        $this->session = $session;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->session->pin);
    }

    public function broadcastAs()
    {
        return 'session.started';
    }

    public function broadcastWith()
    {
        $session = $this->session->load('exam.questions');
        
        return [
            'session' => [
                'id' => $session->id,
                'pin' => $session->pin,
                'status' => $session->status,
                'exam_id' => $session->exam_id,
                'config' => $session->config ?? (object)[], // Return empty object, not array
            ],
            'message' => 'The live session is starting!',
        ];
    }
}
