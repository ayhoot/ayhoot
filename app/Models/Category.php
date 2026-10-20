<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'logo'];

    public function courses()
    {
        return $this->hasMany(Course::class); 
    }

    public function exams()
    {
        return $this->hasMany(Exam::class); 
    }


    /**
     * Get the avatar image URL with default fallback
     */
    public function getLogoAttribute($value)
    {
        if (!$value) {
            return '/imgHolders/pg2.jpg';
        }
        
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        
        return '' . ltrim($value, '/');
    }


}
