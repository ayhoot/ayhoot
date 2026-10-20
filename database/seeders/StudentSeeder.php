<?php

namespace Database\Seeders;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Student::create([
            'first_name' => 'Student',
            'last_name' => 'Student',
            'email' => 'student@gmail.com',
            'username' => 'student',
            'password' => 'student',
            'verified_at'=>Carbon::now()
        ]);

         Student::create([
            'first_name' => 'Student1',
            'last_name' => 'Student1',
            'email' => 'student1@gmail.com',
            'username' => 'student1',
            'password' => 'student1',
            'verified_at'=>Carbon::now()
        ]);

        
    }
}
