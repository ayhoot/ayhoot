<?php

namespace App\Events;

use App\Models\LiveSession;
use App\Models\LiveSessionParticipant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionParticipantJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $participant;

    public function __construct(LiveSession $session, LiveSessionParticipant $participant)
    {
        $this->session = $session;
        $this->participant = $participant;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->session->pin);
    }

    public function broadcastAs()
    {
        return 'participant.joined';
    }

    public function broadcastWith()
    {
        $data = [
            'participant' => [
                'id' => $this->participant->id,
                'student_id' => $this->participant->student_id,
                'name' => $this->participant->nickname ?: ($this->participant->student ? $this->participant->student->name : 'Guest'),
                'nickname' => $this->participant->nickname,
                'avatar' => $this->participant->avatar ?: ($this->participant->student ? $this->participant->student->avatar : '😀'),
                'joined_at' => $this->participant->joined_at,
                'total_score' => $this->participant->total_score ?? 0,
                'correct_answers' => $this->participant->correct_answers ?? 0,
                'current_rank' => $this->participant->current_rank,
                'status' => $this->participant->status,
            ],
            'participant_count' => $this->session->getParticipantCount(),
        ];
        
        \Log::info('[BROADCAST] ===== SENDING participant.joined EVENT =====', [
            'channel' => 'live-session.' . $this->session->pin,
            'event_name' => 'participant.joined',
            'session_id' => $this->session->id,
            'session_pin' => $this->session->pin,
            'participant_id' => $this->participant->id,
            'participant_name' => $data['participant']['name'],
            'broadcast_driver' => config('broadcasting.default'),
            'pusher_config' => [
                'key' => config('broadcasting.connections.pusher.key'),
                'host' => config('broadcasting.connections.pusher.options.host'),
                'port' => config('broadcasting.connections.pusher.options.port'),
            ],
        ]);
        
        return $data;
    }
}
