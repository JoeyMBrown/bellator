<?php

namespace Database\Factories;

use App\Models\MetricUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MetricUnit>
 */
class MetricUnitFactory extends Factory
{
    protected $model = MetricUnit::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
