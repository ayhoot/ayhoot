<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'token',
        'description',
        'logo',
        'teacher_id',
        'institute_id',
        'locale'
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'group_student');
    }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_group');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->token)) {
                $group->token = Str::random(32);
            }
            if (empty($group->locale)) {
                $group->locale = \App\Services\LocaleService::getStoreLocale();
            }
        });
    }
    public function groupAssignments()
    {
        return $this->hasMany(GroupAssignment::class);
    }

    

    
}
