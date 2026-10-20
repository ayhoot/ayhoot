<?php

namespace App\Events;

use App\Models\LiveSession;
use App\Models\Question;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionQuestionNext implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $question;

    public function __construct(LiveSession $session, Question $question)
    {
        $this->session = $session;
        $this->question = $question;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->session->pin);
    }

    public function broadcastAs()
    {
        return 'question.next';
    }

    public function broadcastWith()
    {
        $question = $this->question;
        
        // Ensure all JSON fields are strings for consistent serialization
        return [
            'session' => [
                'id' => $this->session->id,
                'pin' => $this->session->pin,
                'status' => $this->session->status,
                'current_question_index' => $this->session->current_question_index,
                'exam_id' => $this->session->exam_id,
                'config' => $this->session->config ?? (object)[], // Include configuration
            ],
            'question' => [
                'id' => $question->id,
                'exam_id' => $question->exam_id,
                'title' => $question->title,
                'type' => $question->type,
                'options' => is_string($question->options) ? $question->options : json_encode($question->options ?? []),
                'q_options' => is_string($question->q_options) ? $question->q_options : json_encode($question->q_options ?? []),
                'files' => is_string($question->files) ? $question->files : json_encode($question->files ?? []),
                'duration' => $question->duration ?? 30,
                'points' => $question->points ?? 0,
                'slide' => $question->slide,
                'sequence_options' => is_string($question->sequence_options) ? $question->sequence_options : json_encode($question->sequence_options ?? []),
            ],
            'question_index' => $this->session->current_question_index,
            'total_questions' => $this->session->exam->questions->count(),
        ];
    }
}
