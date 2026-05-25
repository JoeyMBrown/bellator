<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutExerciseLog;

class WorkoutExerciseLogPolicy
{
    public function view(User $user, WorkoutExerciseLog $log): bool
    {
        $workout = $log->workoutExercise?->workout;

        if ($workout === null) {
            return false;
        }

        if ($workout->user_id === $user->id) {
            return true;
        }

        return $workout->groups()
            ->whereIn('groups.id', $user->groups()->pluck('groups.id'))
            ->exists();
    }

    public function create(User $user, WorkoutExerciseLog $log): bool
    {
        return $log->workoutExercise?->workout?->user_id === $user->id;
    }

    public function update(User $user, WorkoutExerciseLog $log): bool
    {
        return $log->workoutExercise?->workout?->user_id === $user->id;
    }

    public function delete(User $user, WorkoutExerciseLog $log): bool
    {
        return $log->workoutExercise?->workout?->user_id === $user->id;
    }
}
