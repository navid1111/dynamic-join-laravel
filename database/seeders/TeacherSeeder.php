<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 3) as $index) {
            DB::table('teachers')->insert([
                'name' => $faker->name,
                'subject_id' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
