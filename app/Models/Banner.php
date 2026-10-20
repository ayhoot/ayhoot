<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'image_url', 'city_id', 'user_type', 'is_active', 'display_order'];

    protected $visible = ['id', 'title', 'description', 'image_url', 'city_id', 'user_type', 'is_active', 'display_order', 'city', 'created_at', 'updated_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the city this banner belongs to
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
