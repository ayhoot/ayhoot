<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherRequest extends Model
{
    use HasFactory;
    protected $table = 'institute_teacher_requests';

    protected $fillable = [
        'teacher_id',
        'institute_id',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
