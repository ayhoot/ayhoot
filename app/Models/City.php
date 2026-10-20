<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name_en', 'name_fa', 'slug'];

    protected $visible = ['id', 'name_en', 'name_fa', 'slug'];

    /**
     * Get the banners for this city
     */
    public function banners()
    {
        return $this->hasMany(Banner::class);
    }
}
