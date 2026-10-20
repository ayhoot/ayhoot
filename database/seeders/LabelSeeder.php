<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Label::create([
            'title' => 'Recommended',
            'description' => 'Recommended', 
            'logo' => null,
            'color' => '#FFA500',
        ]);
        Label::create([
            'title' => 'Verified',
            'description' => 'Verified', 
            'logo' => null,
            'color' => '#00C853',
        ]);
    }
}
