<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetricUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('metric_units')->insert([
            ['name' => 'Lbs', 'description' => 'Weight in pounds', 'created_at' => $now],
            ['name' => 'Kg', 'description' => 'Weight in kilograms', 'created_at' => $now],
            ['name' => 'Miles', 'description' => 'Distance in miles', 'created_at' => $now],
            ['name' => 'Yards', 'description' => 'Distance in yards', 'created_at' => $now],
            ['name' => 'Kilometers', 'description' => 'Distance in kilometers', 'created_at' => $now],
            ['name' => 'Meters', 'description' => 'Distance in meters', 'created_at' => $now],
            ['name' => 'Seconds', 'description' => 'Time in seconds', 'created_at' => $now],
        ]);
    }
}
