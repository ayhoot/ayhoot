<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'institute_id',
        'teacher_id',
        'exam_id',
        'live_session_id',
        'start_at',
        'end_at',
        'question_time',
        'randomize_answer'
    ];

    protected $with=[
        'institute',
        'teacher',
        'exam'
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function liveSession()
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'game_group');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
