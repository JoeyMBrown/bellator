<?php

namespace Database\Factories;

use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutExerciseLog>
 */
class WorkoutExerciseLogFactory extends Factory
{
    protected $model = WorkoutExerciseLog::class;

    public function definition(): array
    {
        return [
            'workout_exercise_id' => WorkoutExercise::factory(),
            'repetitions' => fake()->numberBetween(1, 15),
            'exercise_metric' => fake()->randomFloat(2, 1, 250),
            'metric_unit_id' => null,
        ];
    }
}
