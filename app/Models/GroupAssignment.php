<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupAssignment extends Model
{
    use HasFactory;

    protected $table = 'group_assignments';
    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'institute_id',
        'teacher_id',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}