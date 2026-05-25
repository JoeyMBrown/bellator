<?php

namespace Tests\Unit\Policies;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Policies\GroupPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected GroupPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new GroupPolicy;
    }

    public function test_member_can_view_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $this->assertTrue($this->policy->view($user, $group));
    }

    public function test_non_member_cannot_view_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();

        $this->assertFalse($this->policy->view($user, $group));
    }

    public function test_admin_can_update_group(): void
    {
        $admin = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);

        $this->assertTrue($this->policy->update($admin, $group));
    }

    public function test_member_cannot_update_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $this->assertFalse($this->policy->update($user, $group));
    }

    public function test_owner_can_delete_group(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();

        $this->assertTrue($this->policy->delete($owner, $group));
    }

    public function test_admin_cannot_delete_group(): void
    {
        $admin = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);

        $this->assertFalse($this->policy->delete($admin, $group));
    }

    public function test_owner_cannot_leave_group(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();

        $this->assertFalse($this->policy->leave($owner, $group));
    }

    public function test_member_can_leave_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $this->assertTrue($this->policy->leave($user, $group));
    }

    public function test_owner_can_promote_admin(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();

        $this->assertTrue($this->policy->promoteAdmin($owner, $group));
    }

    public function test_admin_cannot_promote_other_admin(): void
    {
        $admin = User::factory()->create();
        $group = Group::factory()->withOwner(User::factory()->create())->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);

        $this->assertFalse($this->policy->promoteAdmin($admin, $group));
    }

    public function test_any_user_can_create_a_group(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->create($user));
    }
}
