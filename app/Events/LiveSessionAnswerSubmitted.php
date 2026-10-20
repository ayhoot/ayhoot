<?php

namespace App\Events;

use App\Models\LiveSession;
use App\Models\LiveSessionAnswer;
use App\Models\LiveSessionParticipant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionAnswerSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $answer;
    public $participant;

    public function __construct(LiveSession $session, LiveSessionAnswer $answer, LiveSessionParticipant $participant)
    {
        $this->session = $session;
        $this->answer = $answer;
        $this->participant = $participant;
    }

    public function broadcastOn()
    {
        // Use public channel for simpler subscription (no auth required)
        return new Channel('live-session.' . $this->session->pin);
    }

    public function broadcastAs()
    {
        return 'answer.submitted';
    }

    public function broadcastWith()
    {
        return [
            'participant' => [
                'id' => $this->participant->id,
                'name' => $this->participant->getDisplayName(),
                'avatar' => $this->participant->getDisplayAvatar(),
                'current_rank' => $this->participant->current_rank,
                'total_score' => $this->participant->total_score,
                'correct_answers' => $this->participant->correct_answers,
                'average_answer_time' => $this->participant->average_answer_time,
            ],
            'answer' => [
                'question_index' => $this->answer->question_index,
                'selected_option_id' => $this->answer->selected_option_id ?? null,
                'answer_text' => $this->answer->answer_text ?? null,
                'is_correct' => $this->answer->is_correct,
                'points_earned' => $this->answer->points_earned,
                'answer_time_ms' => $this->answer->answer_time_ms,
            ],
            'total_answers' => $this->session->answers()
                ->where('question_id', $this->answer->question_id)
                ->count(),
        ];
    }
}
