<?php

namespace Tests\Feature\Groups;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_join_group_by_code(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();

        $this->actingAs($joiner)
            ->post(route('groups.join'), [
                'invite_code' => $group->invite_code,
            ])
            ->assertRedirect(route('groups.show', $group));

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $joiner->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);
    }

    public function test_join_with_invalid_code_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('onboarding'))
            ->post(route('groups.join'), [
                'invite_code' => 'BELL-NOPE0000',
            ])
            ->assertSessionHasErrors('invite_code');
    }

    public function test_member_can_leave_group(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $member->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $this->actingAs($member)
            ->delete(route('groups.leave', $group))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('group_members', [
            'group_id' => $group->id,
            'user_id' => $member->id,
            'deleted_at' => null,
        ]);
    }

    public function test_owner_cannot_leave_group(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();

        $this->actingAs($owner)
            ->delete(route('groups.leave', $group))
            ->assertForbidden();
    }

    public function test_admin_can_remove_member(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $target = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $target->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $this->actingAs($admin)
            ->delete(route('groups.members.destroy', [$group, $target]))
            ->assertRedirect(route('groups.edit', $group));

        $this->assertDatabaseMissing('group_members', [
            'group_id' => $group->id,
            'user_id' => $target->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_cannot_remove_owner(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->delete(route('groups.members.destroy', [$group, $owner]));

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $owner->id,
            'deleted_at' => null,
        ]);
    }

    public function test_owner_can_promote_member_to_admin(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $member->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $this->actingAs($owner)
            ->patch(route('groups.members.role', [$group, $member]), [
                'role' => GroupMember::ROLE_ADMIN,
            ])
            ->assertRedirect(route('groups.edit', $group));

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $member->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);
    }

    public function test_admin_cannot_promote_member(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $member->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $this->actingAs($admin)
            ->patch(route('groups.members.role', [$group, $member]), [
                'role' => GroupMember::ROLE_ADMIN,
            ])
            ->assertForbidden();
    }

    public function test_owner_role_change_is_a_noop(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();

        $this->actingAs($owner)
            ->patch(route('groups.members.role', [$group, $owner]), [
                'role' => GroupMember::ROLE_MEMBER,
            ]);

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $owner->id,
            'role' => GroupMember::ROLE_OWNER,
        ]);
    }
}
