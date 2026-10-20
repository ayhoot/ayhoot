<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSessionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_session_id',
        'student_id',
        'question_id',
        'question_index',
        'selected_option_id',
        'answer_text',
        'answered_at',
        'answer_time_ms',
        'time_remaining_ms',
        'is_correct',
        'points_earned',
        'current_rank_after_answer',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'answer_time_ms' => 'integer',
        'time_remaining_ms' => 'integer',
        'is_correct' => 'boolean',
        'points_earned' => 'integer',
        'current_rank_after_answer' => 'integer',
    ];

    // Relationships
    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    // Helper methods
    public function calculatePoints($timeLimitMs, $basePoints = 1000): int
    {
        if (!$this->is_correct) {
            return 0;
        }

        // Bonus points for answering quickly
        $timeBonus = max(0, ($timeLimitMs - $this->answer_time_ms) / $timeLimitMs) * 500;
        
        return (int) ($basePoints + $timeBonus);
    }

    public function getFormattedAnswerTime(): string
    {
        $seconds = $this->answer_time_ms / 1000;
        return number_format($seconds, 2) . 's';
    }
}
