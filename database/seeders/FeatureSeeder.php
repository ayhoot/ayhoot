<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data=[
            [
                'role'=>'max_student',
                'title'=>'Number of students',
                'value'=>10,
            ],
            [
                'role'=>'max_group',
                'title'=>'Number of groups',
                'value'=>12,
            ],
             [
                'role'=>'create_exam',
                'title'=>'Create Exam',
                'value'=>12,
            ],
        ];


        foreach ($data as $d) {
            Feature::create($d);
        }
        
    }
}
