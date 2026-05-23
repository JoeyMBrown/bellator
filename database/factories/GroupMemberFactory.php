<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupMember>
 */
class GroupMemberFactory extends Factory
{
    protected $model = GroupMember::class;

    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'user_id' => User::factory(),
            'role' => GroupMember::ROLE_MEMBER,
            'joined_at' => now(),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn () => ['role' => GroupMember::ROLE_OWNER]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => GroupMember::ROLE_ADMIN]);
    }
}
