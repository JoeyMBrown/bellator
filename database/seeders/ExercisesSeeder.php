<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExercisesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('exercises')->insert([
            ['name' => 'Barbell Bench Press', 'description' => 'Bench press using a barbell', 'created_at' => '2024-11-30 19:42:00'],
            ['name' => 'Barbell Squat', 'description' => 'Squat using a barbell', 'created_at' => '2024-11-30 19:42:00'],
            ['name' => 'Run', 'description' => 'Running some distance', 'created_at' => '2024-11-30 19:42:00'],
            ['name' => 'Sprint', 'description' => 'Sprinting some distance', 'created_at' => '2024-11-30 19:42:00'],
            ['name' => 'Swim', 'description' => 'Swimming some distance', 'created_at' => '2024-11-30 19:42:00']
        ]);
    }
}
