<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'plan_id',
        'exam_id',
        'course_id',
        'game_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'description',
        'amount',
        'currency',
        'teacher_id',
        'institute_id',
        'student_id',
        'transaction_id',
        'meta',          
    ];
    protected $casts = [
        'plan_id' => 'integer',
        'exam_id' => 'integer',
        'course_id' => 'integer',
        'game_id' => 'integer',
        'teacher_id' => 'integer',
        'institute_id' => 'integer',
        'student_id' => 'integer',
        'meta' => 'array',
        
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }


    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
}
