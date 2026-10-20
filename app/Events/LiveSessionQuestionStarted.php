<?php

namespace App\Events;

use App\Models\LiveSession;
use App\Models\Question;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionQuestionStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $question;
    public $timeLimit;

    public function __construct(LiveSession $session, Question $question, $timeLimit)
    {
        $this->session = $session;
        $this->question = $question;
        $this->timeLimit = $timeLimit;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->session->pin);
    }

    public function broadcastAs()
    {
        return 'question.started';
    }

    public function broadcastWith()
    {
        $question = $this->question;
        
        // Get the start time in ISO 8601 format for JavaScript
        $startedAt = $this->session->current_question_started_at;
        $startedAtISO = $startedAt ? $startedAt->toIso8601String() : now()->toIso8601String();
        
        // Ensure all JSON fields are strings for consistent serialization
        return [
            'session' => [
                'id' => $this->session->id,
                'pin' => $this->session->pin,
                'status' => $this->session->status,
                'current_question_index' => $this->session->current_question_index,
                'exam_id' => $this->session->exam_id,
                'config' => $this->session->config ?? (object)[], // Return empty object, not array
            ],
            'question' => [
                'id' => $question->id,
                'exam_id' => $question->exam_id,
                'title' => $question->title,
                'type' => $question->type,
                'options' => $question->options, // Accessor already returns array
                'q_options' => $question->q_options, // Accessor already returns array
                'files' => is_array($question->files) ? $question->files : (is_string($question->files) ? json_decode($question->files, true) : []),
                'duration' => $question->duration ?? 30,
                'points' => $question->points ?? 0,
                'slide' => $question->slide,
                'sequence_options' => $question->sequence_options, // Accessor already returns array
            ],
            'time_limit_ms' => $this->timeLimit,
            'question_index' => $this->session->current_question_index,
            'total_questions' => $this->session->exam->questions->count(),
            'started_at' => $startedAtISO, // ISO 8601 format for JavaScript Date parsing
            'server_time' => now()->toIso8601String(), // Current server time for clock sync
        ];
    }
}
