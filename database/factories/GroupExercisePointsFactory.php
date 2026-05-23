<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupExercisePoints;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupExercisePoints>
 */
class GroupExercisePointsFactory extends Factory
{
    protected $model = GroupExercisePoints::class;

    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'exercise_id' => Exercise::factory(),
            'points_per_unit' => fake()->randomFloat(6, 0.001, 5),
            'start_date' => now(),
            'end_date' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['end_date' => now()]);
    }
}
