<?php

namespace Tests\Unit\Policies;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupExercisePoints;
use App\Models\GroupMember;
use App\Models\User;
use App\Policies\GroupExercisePointsPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupExercisePointsPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected GroupExercisePointsPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new GroupExercisePointsPolicy;
    }

    public function test_member_can_view_points(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $this->assertTrue($this->policy->viewAny($user, $group));
    }

    public function test_non_member_cannot_view_points(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();

        $this->assertFalse($this->policy->viewAny($user, $group));
    }

    public function test_admin_can_update_points(): void
    {
        $admin = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);

        $exercise = Exercise::factory()->forGroup($group)->create();
        $points = GroupExercisePoints::factory()->create([
            'group_id' => $group->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->assertTrue($this->policy->update($admin, $points));
    }

    public function test_member_cannot_update_points(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $exercise = Exercise::factory()->forGroup($group)->create();
        $points = GroupExercisePoints::factory()->create([
            'group_id' => $group->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->assertFalse($this->policy->update($user, $points));
    }
}
