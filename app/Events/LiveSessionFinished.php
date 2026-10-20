<?php

namespace App\Events;

use App\Models\LiveSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionFinished implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $finalRankings;

    public function __construct(LiveSession $session, $finalRankings)
    {
        $this->session = $session;
        $this->finalRankings = $finalRankings;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->session->pin);
    }

    public function broadcastAs()
    {
        return 'session.finished';
    }

    public function broadcastWith()
    {
        return [
            'session' => $this->session,
            'final_rankings' => $this->finalRankings->map(function($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->getDisplayName(),
                    'avatar' => $participant->getDisplayAvatar(),
                    'rank' => $participant->current_rank,
                    'score' => $participant->total_score,
                    'correct_answers' => $participant->correct_answers,
                    'average_answer_time' => $participant->average_answer_time,
                ];
            }),
            'total_participants' => $this->session->getParticipantCount(),
            'message' => '🎉 Congratulations! The live session has finished!',
        ];
    }
}
