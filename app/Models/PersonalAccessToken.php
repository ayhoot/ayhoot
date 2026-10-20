<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    use HasFactory;
    protected $fillable = [
        'tokenable_id',
        'tokenable_type',
        'name',
        'token',
        'abilities',
        'institute_id',
        'last_used_at',
        'expires_at',
    ];


    protected $table = 'personal_access_tokens';
    public function tokenable()
    {
        return $this->morphTo();
    }
    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
    public function getNameAttribute()
    {
        return $this->tokenable->name;
    }
    public function getTokenAttribute()
    {
        return $this->tokenable->token;
    }
}
