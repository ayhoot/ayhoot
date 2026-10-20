<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'teacher_id', 'institute_id', 'locale'];
    protected $with = ['exams', 'institute', 'teacher'];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'folder_exam')->withTimestamps();
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_folder')->withTimestamps();
    }

    /**
     * Boot method to set locale when creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($folder) {
            if (empty($folder->locale)) {
                $folder->locale = \App\Services\LocaleService::getStoreLocale();
            }
        });
    }
}
