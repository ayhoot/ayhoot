<?php

namespace App\Events;

use App\Models\LiveSession;
use App\Models\Question;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionQuestionResults implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $question;
    public $answers;
    public $rankings;

    public function __construct(LiveSession $session, Question $question, $answers, $rankings)
    {
        $this->session = $session;
        $this->question = $question;
        $this->answers = $answers;
        $this->rankings = $rankings;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->session->pin);
    }

    public function broadcastAs()
    {
        return 'question.results';
    }

    public function broadcastWith()
    {
        // Parse options from JSON
        $options = is_string($this->question->options) 
            ? json_decode($this->question->options, true) ?? []
            : ($this->question->options ?? []);

        $optionStats = [];
        $totalAnswers = $this->answers->count();

        // Build stats from JSON options
        foreach ($options as $option) {
            $optionId = $option['id'] ?? null;
            if (!$optionId) continue;

            $optionAnswers = $this->answers->where('selected_option_id', $optionId);
            $optionStats[] = [
                'option' => [
                    'id' => $optionId,
                    'option_text' => $option['text'] ?? $option['option_text'] ?? '',
                    'text' => $option['text'] ?? '',
                    'is_correct' => $option['correct'] ?? false,
                    'image' => $option['image'] ?? null,
                ],
                'count' => $optionAnswers->count(),
                'percentage' => $totalAnswers > 0 ? ($optionAnswers->count() / $totalAnswers) * 100 : 0,
                'participants' => $optionAnswers->map(function($answer) {
                    return [
                        'name' => $answer->participant->student->name ?? $answer->participant->nickname ?? 'Guest',
                        'avatar' => $answer->participant->avatar ?? '/images/default-avatar.png',
                        'answer_time_ms' => $answer->answer_time_ms,
                    ];
                }),
            ];
        }

        // Find correct option
        $correctOption = null;
        foreach ($options as $option) {
            if ($option['correct'] ?? false) {
                $correctOption = $option;
                break;
            }
        }

        return [
            'question' => $this->question,
            'option_stats' => $optionStats,
            'correct_option' => $correctOption,
            'rankings' => $this->rankings->take(10)->map(function($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->getDisplayName(),
                    'avatar' => $participant->getDisplayAvatar(),
                    'rank' => $participant->current_rank,
                    'score' => $participant->total_score,
                    'correct_answers' => $participant->correct_answers,
                ];
            }),
            'total_participants' => $this->session->getParticipantCount(),
        ];
    }
}
