<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstituteInvite extends Model
{
    use HasFactory;

    protected $fillable=[
        'email',
        'institute_id',
        'token',
        'sent_at',
        'accepted_at'
    ];
    protected $hidden = ['token'];
    protected $casts = [
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];
    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }   
}
