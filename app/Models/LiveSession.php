<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'pin',
        'title',
        'description',
        'host_id',
        'host_type',
        'exam_id',
        'status',
        'started_at',
        'finished_at',
        'current_question_index',
        'current_question_time_limit',
        'current_question_started_at',
        'show_ranking_after_each_question',
        'show_correct_answer',
        'settings',
        'config',
        'allow_late_join',
        'show_leaderboard',
        'randomize_questions',
        'randomize_options',
        'default_question_time',
        'hide_time',
        'ignore_time_counting',
        'teacher_paced',
        'show_ranking_after_question',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'current_question_started_at' => 'datetime',
        'settings' => 'array',
        'config' => 'array',
        'show_ranking_after_each_question' => 'boolean',
        'show_correct_answer' => 'boolean',
        'allow_late_join' => 'boolean',
        'show_leaderboard' => 'boolean',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'hide_time' => 'boolean',
        'ignore_time_counting' => 'boolean',
        'teacher_paced' => 'boolean',
        'show_ranking_after_question' => 'boolean',
    ];
    
    protected $attributes = [
        'config' => '{"auto_advance":true}', // Default config
    ];

    // Relationships
    public function host(): MorphTo
    {
        return $this->morphTo();
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(LiveSessionParticipant::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(LiveSessionAnswer::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'started', 'question']);
    }

    public function scopeByPin($query, $pin)
    {
        return $query->where('pin', $pin);
    }

    // Helper methods
    public function isActive(): bool
    {
        return in_array($this->status, ['waiting', 'started', 'question']);
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    public function getCurrentQuestion()
    {
        if ($this->current_question_index >= $this->exam->questions->count()) {
            return null;
        }

        return $this->exam->questions()
            ->orderBy('id')
            ->skip($this->current_question_index)
            ->first();
    }

    public function getParticipantCount(): int
    {
        return $this->participants()->where('status', '!=', 'disconnected')->count();
    }

    public function getLeaderboard($limit = 10)
    {
        return $this->participants()
            ->where('status', '!=', 'disconnected')
            ->orderBy('total_score', 'desc')
            ->orderBy('total_answer_time', 'asc')
            ->limit($limit)
            ->get();
    }

    public function generatePin(): string
    {
        do {
            $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('pin', $pin)->exists());

        return $pin;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->pin)) {
                $session->pin = $session->generatePin();
            }
        });
    }
}
