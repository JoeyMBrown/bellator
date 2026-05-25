<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\WorkoutExerciseLog;
use App\Models\WorkoutExerciseLogPoints;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutExerciseLogPoints>
 */
class WorkoutExerciseLogPointsFactory extends Factory
{
    protected $model = WorkoutExerciseLogPoints::class;

    public function definition(): array
    {
        return [
            'workout_exercise_log_id' => WorkoutExerciseLog::factory(),
            'group_id' => Group::factory(),
            'points_earned' => fake()->randomFloat(4, 0, 500),
        ];
    }
}
