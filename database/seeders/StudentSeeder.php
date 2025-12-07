<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 20) as $index) {
            DB::table('students')->insert([
                'name' => $faker->name,
                'age' => $faker->numberBetween(15, 25),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
