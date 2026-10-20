<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'log',
        'teacher_id',
        'student_id',
        'admin_id',
        'institute_id',
    ];

    protected $with=['admin','teacher','student','institute'];

    /**
     * Get the teacher associated with the activity.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the student associated with the activity.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the admin associated with the activity.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the institute associated with the activity.
     */
    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
