<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkoutTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::where('email', 'admin@bellator.com')->first();

        DB::table('workout_types')->insert([
            [
                'name' => 'Anerobic',
                'description' => 'Anerobic workout',
                'created_at' => '2025-09-21 19:31:00',
                'created_by_user_id' => $user->id
            ],
            [
                'name' => 'Aerobic',
                'description' => 'Aerobic workout',
                'created_at' => '2025-09-21 19:31:00',
                'created_by_user_id' => $user->id
            ],
            [
                'name' => 'Mixed',
                'description' => 'Mixed workout',
                'created_at' => '2025-09-21 19:31:00',
                'created_by_user_id' => $user->id
            ]
        ]);
    }
}
