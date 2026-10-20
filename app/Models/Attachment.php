<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['file_path', 'file_type'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    // Accessor for full file URL
    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
