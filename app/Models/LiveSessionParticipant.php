<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSessionParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_session_id',
        'student_id',
        'nickname',
        'avatar',
        'status',
        'joined_at',
        'finished_at',
        'total_score',
        'correct_answers',
        'current_rank',
        'total_answer_time',
        'average_answer_time',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'finished_at' => 'datetime',
        'total_score' => 'integer',
        'correct_answers' => 'integer',
        'current_rank' => 'integer',
        'total_answer_time' => 'integer',
        'average_answer_time' => 'integer',
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

    public function answers()
    {
        return $this->hasMany(LiveSessionAnswer::class, 'live_session_id', 'live_session_id')
                    ->where('student_id', $this->student_id);
    }

    // Helper methods
    public function updateRank($newRank): void
    {
        $this->current_rank = $newRank;
        $this->save();
    }

    public function addScore($points): void
    {
        $this->total_score += $points;
        $this->save();
    }

    public function incrementCorrectAnswers(): void
    {
        $this->correct_answers += 1;
        $this->save();
    }

    public function updateAverageAnswerTime($answerTimeMs): void
    {
        $this->total_answer_time += $answerTimeMs;
        $answersCount = $this->answers()->count();
        $this->average_answer_time = $answersCount > 0 ? $this->total_answer_time / $answersCount : 0;
        $this->save();
    }

    public function getDisplayName(): string
    {
        return (string) ($this->nickname ?: (optional($this->student)->name ?: 'Participant'));
    }

    public function getDisplayAvatar(): ?string
    {
        return $this->avatar ?: (optional($this->student)->avatar ?: null);
    }
}
