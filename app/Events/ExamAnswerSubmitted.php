<?php

namespace App\Events;

use App\Models\Answer;
use App\Models\Exam;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamAnswerSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $answer;
    public $exam;
    public $student;

    public function __construct($answer, Exam $exam, $student)
    {
        $this->answer = $answer;
        $this->exam = $exam;
        $this->student = $student;
    }

    public function broadcastOn()
    {
        // Use public channel instead of private for easier access
        return new Channel('exam.' . $this->exam->id);
    }

    public function broadcastAs()
    {
        return 'answer.submitted';
    }

    public function broadcastWith()
    {
        return [
            'answer' => [
                'id' => is_object($this->answer) ? $this->answer->id : ($this->answer['id'] ?? null),
                'question_id' => is_object($this->answer) ? $this->answer->question_id : ($this->answer['question_id'] ?? null),
                'selected_option_id' => is_object($this->answer) ? $this->answer->selected_option_id : ($this->answer['selected_option_id'] ?? null),
                'is_correct' => is_object($this->answer) ? $this->answer->is_correct : ($this->answer['is_correct'] ?? false),
                'created_at' => is_object($this->answer) ? $this->answer->created_at : ($this->answer['created_at'] ?? now()),
            ],
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->name,
                'email' => $this->student->email ?? '',
            ],
            'exam_id' => $this->exam->id,
            'timestamp' => now()->toISOString(),
        ];
    }
}
