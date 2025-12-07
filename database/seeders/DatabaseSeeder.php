<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\StudentSeeder; 
use Database\Seeders\TeacherSeeder; 
use Database\Seeders\SubjectSeeder; 
use Database\Seeders\ResultSeeder; 
use Database\Seeders\ReportSeeder; 
use Database\Seeders\UserSeeder; 


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            StudentSeeder::class,
            TeacherSeeder::class,
            SubjectSeeder::class,
            ResultSeeder::class,
            ReportSeeder::class,
            UserSeeder::class,
        ]);
    }
}
