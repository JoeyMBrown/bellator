<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exercise>
 */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'group_id' => Group::factory(),
            'measurement_type' => fake()->randomElement(Exercise::MEASUREMENT_TYPES),
            'created_by_user_id' => User::factory(),
        ];
    }

    public function forGroup(Group $group): static
    {
        return $this->state(fn () => ['group_id' => $group->id]);
    }

    public function repsOnly(): static
    {
        return $this->state(fn () => ['measurement_type' => Exercise::MEASUREMENT_REPS_ONLY]);
    }

    public function weightedReps(): static
    {
        return $this->state(fn () => ['measurement_type' => Exercise::MEASUREMENT_WEIGHTED_REPS]);
    }

    public function distance(): static
    {
        return $this->state(fn () => ['measurement_type' => Exercise::MEASUREMENT_DISTANCE]);
    }

    public function duration(): static
    {
        return $this->state(fn () => ['measurement_type' => Exercise::MEASUREMENT_DURATION]);
    }
}
