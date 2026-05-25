<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workout;

class WorkoutPolicy
{
    public function view(User $user, Workout $workout): bool
    {
        if ($workout->user_id === $user->id) {
            return true;
        }

        return $workout->groups()
            ->whereIn('groups.id', $user->groups()->pluck('groups.id'))
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->groupMemberships()->exists();
    }

    public function update(User $user, Workout $workout): bool
    {
        return $workout->user_id === $user->id;
    }

    public function delete(User $user, Workout $workout): bool
    {
        return $workout->user_id === $user->id;
    }
}
