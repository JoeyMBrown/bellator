<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'invite_code' => 'BELL-'.strtoupper(Str::random(8)),
            'timezone' => 'UTC',
            'created_by_user_id' => User::factory(),
        ];
    }

    public function withOwner(User $user): static
    {
        return $this
            ->state(fn () => ['created_by_user_id' => $user->id])
            ->afterCreating(function (Group $group) use ($user) {
                GroupMember::factory()->create([
                    'group_id' => $group->id,
                    'user_id' => $user->id,
                    'role' => GroupMember::ROLE_OWNER,
                ]);
            });
    }
}
