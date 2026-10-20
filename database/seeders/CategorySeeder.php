<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        // Create two categories
        DB::table('categories')->insert([
            [
                'title' => 'Category 1',
                'description' => 'Description for Category 1',
                'logo' => 'imgHolders/bg1.jpg',
                'updated_at'=> now(),
                'created_at'=> now()
            ],
            [
                'title' => 'Category 2',
                'description' => 'Description for Category 2',
                'logo' => 'imgHolders/bg1.jpg',
                'updated_at'=> now(),
                'created_at'=> now()
            ],
        ]);
    }
}
