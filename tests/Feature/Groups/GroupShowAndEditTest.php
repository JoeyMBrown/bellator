<?php

namespace Tests\Feature\Groups;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupShowAndEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_view_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();

        $this->actingAs($user)
            ->get(route('groups.show', $group))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Groups/Show')
                ->where('group.id', $group->id)
                ->where('group.name', $group->name)
                ->where('group.role', 'owner')
            );
    }

    public function test_non_member_cannot_view_group(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        // Stranger has their own group so the onboarding gate passes
        // and we exercise the policy denial, not the middleware redirect.
        Group::factory()->withOwner($stranger)->create();

        $this->actingAs($stranger)
            ->get(route('groups.show', $group))
            ->assertForbidden();
    }

    public function test_user_with_no_group_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();

        $this->actingAs($user)
            ->get(route('groups.show', $group))
            ->assertRedirect(route('onboarding'));
    }

    public function test_admin_can_view_edit(): void
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
            ->get(route('groups.edit', $group))
            ->assertOk();
    }

    public function test_member_cannot_view_edit(): void
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
            ->get(route('groups.edit', $group))
            ->assertForbidden();
    }

    public function test_admin_can_update_group_details(): void
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
            ->put(route('groups.update', $group), [
                'name' => 'Renamed',
                'description' => 'New description',
                'timezone' => 'UTC',
            ])
            ->assertRedirect(route('groups.edit', $group));

        $this->assertSame('Renamed', $group->fresh()->name);
    }

    public function test_member_cannot_update_group(): void
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
            ->put(route('groups.update', $group), [
                'name' => 'Hacked',
                'timezone' => 'UTC',
            ])
            ->assertForbidden();
    }

    public function test_owner_can_delete_group(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();

        $this->actingAs($owner)
            ->delete(route('groups.destroy', $group))
            ->assertRedirect(route('dashboard'));

        $this->assertSoftDeleted($group);
    }

    public function test_admin_cannot_delete_group(): void
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
            ->delete(route('groups.destroy', $group))
            ->assertForbidden();
    }

    public function test_admin_can_regenerate_invite_code(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);

        $previousCode = $group->invite_code;

        $this->actingAs($admin)
            ->post(route('groups.invite-code.regenerate', $group))
            ->assertRedirect(route('groups.edit', $group));

        $this->assertNotSame($previousCode, $group->fresh()->invite_code);
    }

    public function test_member_cannot_regenerate_invite_code(): void
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
            ->post(route('groups.invite-code.regenerate', $group))
            ->assertForbidden();
    }
}
