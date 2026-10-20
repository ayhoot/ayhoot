<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacher=Teacher::create([
            'first_name' => 'Teacher',
            'last_name' => 'Teacher',
            'email' => 'teacher@gmail.com',
            'username' => 'teacher',
            'password' => 'teacher',
            'plan_id'=>2,
            'verified_at'=>Carbon::now()

        ]);
        // set id 1 and 2 institutes for this teacher
        $teacher->institutes()->attach([1,2]);
        $studentIds = [1, 2];

        if ($teacher) {
            $teacher->students()->syncWithoutDetaching($studentIds);

            $this->command->info("Students assigned to Teacher successfully.");
        } else {
            $this->command->warn("Teacher not found.");
        }
    }
}
