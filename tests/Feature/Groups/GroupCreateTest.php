<?php

namespace Tests\Feature\Groups;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_view_create_form(): void
    {
        $this->get(route('groups.create'))->assertRedirect(route('login'));
    }

    public function test_unverified_user_cannot_view_create_form(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('groups.create'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('groups.create'))
            ->assertOk();
    }

    public function test_verified_user_can_create_group_and_becomes_owner(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('groups.store'), [
                'name' => 'My Crew',
                'description' => 'A test group',
                'timezone' => 'America/New_York',
            ]);

        $group = Group::query()->where('name', 'My Crew')->firstOrFail();

        $response->assertRedirect(route('groups.show', $group));
        $this->assertSame($user->id, $group->created_by_user_id);
        $this->assertNotNull($group->invite_code);
        $this->assertSame('America/New_York', $group->timezone);

        $member = GroupMember::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $this->assertSame(GroupMember::ROLE_OWNER, $member->role);
    }

    public function test_group_creation_validates_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('groups.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->actingAs($user)
            ->post(route('groups.store'), ['name' => str_repeat('a', 61)])
            ->assertSessionHasErrors('name');
    }
}
