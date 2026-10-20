<?php

namespace Database\Seeders;

use App\Models\Exam;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Exam::create([
            'title' => 'Math Exam',
            'description' => 'This is a math exam.',
            'cover' => 'logo.png',
            'monetize' => 'free',
            'price' => null,
            'status' => 'published',
            'institute_id' => 1,
            'teacher_id' => 1,
            'category_id' => 1,
            'folder_id' => 1,
            'locale' => 'en',
        ]);
        Exam::create([
            'title' => 'Science Exam',
            'description' => 'This is a science exam.',
            'cover' => 'logo.png',
            'monetize' => 'paid',
            'price' => 10000.00,
            'status' => 'published',
            'institute_id' => 1,
            'teacher_id' => 1,
            'category_id' => 1,
            'folder_id' => 1,
            'locale' => 'en',
        ]);
    }
}
