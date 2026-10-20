<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'logo',
        'color',
    ];

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_label');
    }

    public function institutes()
    {
        return $this->belongsToMany(Institute::class, 'institute_label');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_label');
    }
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_label');
    }


}
