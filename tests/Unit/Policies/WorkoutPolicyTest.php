<?php

namespace Tests\Unit\Policies;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\Workout;
use App\Policies\WorkoutPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected WorkoutPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new WorkoutPolicy;
    }

    public function test_owner_can_view_their_workout(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->forUser($user)->create();

        $this->assertTrue($this->policy->view($user, $workout));
    }

    public function test_group_member_can_view_workout_tagged_to_their_group(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $group = Group::factory()->withOwner($author)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $viewer->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $workout = Workout::factory()->forUser($author)->forGroup($group)->create();

        $this->assertTrue($this->policy->view($viewer, $workout));
    }

    public function test_non_member_cannot_view_workout(): void
    {
        $author = User::factory()->create();
        $stranger = User::factory()->create();
        $group = Group::factory()->withOwner($author)->create();
        $workout = Workout::factory()->forUser($author)->forGroup($group)->create();

        $this->assertFalse($this->policy->view($stranger, $workout));
    }

    public function test_user_with_group_membership_can_create_workout(): void
    {
        $user = User::factory()->create();
        Group::factory()->withOwner($user)->create();

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_with_no_groups_cannot_create_workout(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->create($user));
    }

    public function test_only_owner_can_update_workout(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $workout = Workout::factory()->forUser($owner)->create();

        $this->assertTrue($this->policy->update($owner, $workout));
        $this->assertFalse($this->policy->update($other, $workout));
    }

    public function test_only_owner_can_delete_workout(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $workout = Workout::factory()->forUser($owner)->create();

        $this->assertTrue($this->policy->delete($owner, $workout));
        $this->assertFalse($this->policy->delete($other, $workout));
    }
}
