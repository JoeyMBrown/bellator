<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workout>
 */
class WorkoutFactory extends Factory
{
    protected $model = Workout::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workout_date' => now(),
            'workout_type_id' => null,
            'notes' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function forGroup(Group $group): static
    {
        return $this->afterCreating(function (Workout $workout) use ($group) {
            $workout->groups()->syncWithoutDetaching([$group->id]);
        });
    }
}
