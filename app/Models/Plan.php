<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'logo',
        'color',
        'price',
        'description',
        'status',
        'is_free',
        'is_popular',
    ];
    protected $with = ['features'];
    protected $casts = [
        'is_free' => 'boolean',
        'is_popular' => 'boolean',
    ];
    public function features()
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withTimestamps();
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
