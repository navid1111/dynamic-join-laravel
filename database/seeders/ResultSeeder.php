<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Assuming you have a subject_id column in the Result table.
        // Inserting results for a few students in different subjects.

        foreach (range(1, 20) as $index) {
            DB::table('results')->insert([
                'student_id' => $index,
                'subject_id' => $faker->numberBetween(1, 3),
                'marks_obtained' => $faker->numberBetween(60, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
