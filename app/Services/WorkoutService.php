<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkoutService
{
    public function __construct(protected WorkoutExerciseLogService $logs) {}

    public function create(User $user, array $data): Workout
    {
        return DB::transaction(function () use ($user, $data) {
            $groupIds = $this->validateUserGroupIds($user, $data['group_ids'] ?? []);

            $workout = Workout::create([
                'user_id' => $user->id,
                'workout_date' => $data['workout_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $workout->groups()->sync($groupIds);

            return $workout;
        });
    }

    public function update(Workout $workout, array $data): Workout
    {
        return DB::transaction(function () use ($workout, $data) {
            $workout->fill([
                'workout_date' => $data['workout_date'] ?? $workout->workout_date,
                'notes' => $data['notes'] ?? $workout->notes,
            ])->save();

            if (array_key_exists('group_ids', $data)) {
                $groupIds = $this->validateUserGroupIds($workout->user, $data['group_ids']);
                $previousIds = $workout->groups()->pluck('groups.id')->all();
                $workout->groups()->sync($groupIds);

                if (array_diff($previousIds, $groupIds) || array_diff($groupIds, $previousIds)) {
                    $this->logs->rebuildPointsForWorkout($workout->fresh(['groups']));
                }
            }

            return $workout->fresh(['groups']);
        });
    }

    public function delete(Workout $workout): void
    {
        $workout->delete();
    }

    /**
     * Ensure all submitted group ids belong to groups the user is a member of.
     *
     * @return array<int, int>
     */
    protected function validateUserGroupIds(User $user, array $submitted): array
    {
        $submitted = array_values(array_unique(array_map('intval', $submitted)));

        if ($submitted === []) {
            throw ValidationException::withMessages([
                'group_ids' => 'Tag the workout to at least one group.',
            ]);
        }

        $allowed = $user->groups()->pluck('groups.id')->all();
        $invalid = array_diff($submitted, $allowed);

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'group_ids' => 'You are not a member of one or more selected groups.',
            ]);
        }

        return $submitted;
    }
}
