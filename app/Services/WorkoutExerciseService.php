<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use Illuminate\Validation\ValidationException;

class WorkoutExerciseService
{
    public function add(Workout $workout, int $exerciseId): WorkoutExercise
    {
        $taggedGroupIds = $workout->groups()->pluck('groups.id');

        $exercise = Exercise::query()
            ->whereIn('group_id', $taggedGroupIds)
            ->find($exerciseId);

        if ($exercise === null) {
            throw ValidationException::withMessages([
                'exercise_id' => 'That exercise is not available in any of the workout’s groups.',
            ]);
        }

        return WorkoutExercise::create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);
    }

    public function remove(WorkoutExercise $workoutExercise): void
    {
        $workoutExercise->workoutExerciseLogs()->each(function ($log) {
            $log->points()->delete();
            $log->delete();
        });

        $workoutExercise->delete();
    }
}
