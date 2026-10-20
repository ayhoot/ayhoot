<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

   
    protected $fillable = [
        'exam_id',
        'student_id',
        'game_id', 
        'answers',
        'started_at',
        'completed_at',
        'status',
        'total_duration_seconds', 
    ];
    protected $casts = [
        'answers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_paid' => 'boolean'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }


    public function getRemainingTimeAttribute(): int
    {
        if (!$this->started_at || $this->status == 'completed' || !$this->total_duration_seconds) {
            return 0;
        }

        $elapsedSeconds = Carbon::now()->diffInSeconds($this->started_at);
        $remaining = $this->total_duration_seconds - $elapsedSeconds;

        return max(0, $remaining); 
    }
}
