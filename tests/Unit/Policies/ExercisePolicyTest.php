<?php

namespace Tests\Unit\Policies;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Policies\ExercisePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExercisePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ExercisePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ExercisePolicy;
    }

    public function test_member_can_view_exercises_in_their_group(): void
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

    public function test_non_member_cannot_view_exercises(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();

        $this->assertFalse($this->policy->viewAny($user, $group));
    }

    public function test_admin_can_update_any_exercise(): void
    {
        $admin = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);

        $exercise = Exercise::factory()->forGroup($group)->create([
            'created_by_user_id' => User::factory()->create()->id,
        ]);

        $this->assertTrue($this->policy->update($admin, $exercise));
    }

    public function test_member_can_update_their_own_exercise(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $exercise = Exercise::factory()->forGroup($group)->create([
            'created_by_user_id' => $user->id,
        ]);

        $this->assertTrue($this->policy->update($user, $exercise));
    }

    public function test_member_cannot_update_others_exercise(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $exercise = Exercise::factory()->forGroup($group)->create([
            'created_by_user_id' => User::factory()->create()->id,
        ]);

        $this->assertFalse($this->policy->update($user, $exercise));
    }

    public function test_non_member_cannot_view_an_exercise(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        $exercise = Exercise::factory()->forGroup($group)->create();

        $this->assertFalse($this->policy->view($user, $exercise));
    }
}
