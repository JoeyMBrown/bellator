<?php

namespace Database\Seeders;

use App\Models\MetricUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetricUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('metric_units')->insert([
            ['name' => 'Lbs', 'description' => 'Weight in pounds', 'created_at' => '2024-11-30 19:42:00'],
            ['name' => 'Kg', 'description' => 'Weight in Kilograms', 'created_at' => '2024-11-30 19:42:00'],
            ['name' => 'Miles', 'description' => 'Distance in Miles', 'created_at' => '2024-11-30 19:42:00'],
            ['name' => 'Yards', 'description' => 'Distance in Yards', 'created_at' => '2024-11-30 19:42:00']
        ]);
    }
}
