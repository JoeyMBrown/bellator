<?php

namespace App\Support;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Collection;

class DefaultExercises
{
    /**
     * @return array<int, array{name: string, description: string, measurement_type: string}>
     */
    public static function definitions(): array
    {
        return [
            [
                'name' => 'Bench Press',
                'description' => 'Barbell or dumbbell bench press.',
                'measurement_type' => Exercise::MEASUREMENT_WEIGHTED_REPS,
            ],
            [
                'name' => 'Squat',
                'description' => 'Barbell back or front squat.',
                'measurement_type' => Exercise::MEASUREMENT_WEIGHTED_REPS,
            ],
            [
                'name' => 'Push-Ups',
                'description' => 'Standard push-ups.',
                'measurement_type' => Exercise::MEASUREMENT_REPS_ONLY,
            ],
            [
                'name' => 'Plank',
                'description' => 'Hold a plank position; logged in seconds.',
                'measurement_type' => Exercise::MEASUREMENT_DURATION,
            ],
            [
                'name' => 'Run',
                'description' => 'Steady-state running.',
                'measurement_type' => Exercise::MEASUREMENT_DISTANCE,
            ],
            [
                'name' => 'Sprint',
                'description' => 'Short, high-intensity running effort.',
                'measurement_type' => Exercise::MEASUREMENT_DISTANCE,
            ],
            [
                'name' => 'Swim',
                'description' => 'Swimming any stroke.',
                'measurement_type' => Exercise::MEASUREMENT_DISTANCE,
            ],
        ];
    }

    /**
     * Insert one copy of each default exercise into the given group.
     *
     * @return Collection<int, Exercise>
     */
    public static function seedForGroup(Group $group, User $creator): Collection
    {
        return collect(self::definitions())->map(fn (array $row) => Exercise::create([
            'group_id' => $group->id,
            'name' => $row['name'],
            'description' => $row['description'],
            'measurement_type' => $row['measurement_type'],
            'created_by_user_id' => $creator->id,
        ]));
    }
}
