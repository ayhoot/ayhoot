<?php

namespace Database\Seeders;

use App\Models\Institute;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstituteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Institute::create([
            'name'=>'Institute 1',
            'username'=>'institute',
            'password'=>'institute',
            'verified_at'=>Carbon::now(),
        ]);

        Institute::create([
            'name'=>'Institute 2',
            'username'=>'institute2',
            'password'=>'institute2',
            'verified_at'=>Carbon::now(),
        ]);
    }
}
