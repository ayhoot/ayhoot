<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            InstituteSeeder::class,
            AdminSeeder::class,
            StudentSeeder::class,
            TeacherSeeder::class,
            FeatureSeeder::class,
            CategorySeeder::class,
            PlanSeeder::class,
            FolderSeeder::class,
            ExamSeeder::class,
            SettingSeeder::class,
            LabelSeeder::class,
            CitySeeder::class,

        ]);
    }
}
