<?php

namespace Tests\Feature\Groups;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupExerciseTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_view_exercise_list(): void
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
            ->get(route('groups.exercises.index', $group))
            ->assertOk();
    }

    public function test_non_member_cannot_view_exercise_list(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        Group::factory()->withOwner($stranger)->create(); // get past onboarding gate

        $this->actingAs($stranger)
            ->get(route('groups.exercises.index', $group))
            ->assertForbidden();
    }

    public function test_member_can_create_exercise(): void
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
            ->post(route('groups.exercises.store', $group), [
                'name' => 'Pull-Ups',
                'description' => 'Bodyweight pull-ups.',
                'measurement_type' => Exercise::MEASUREMENT_REPS_ONLY,
            ])
            ->assertRedirect(route('groups.exercises.index', $group));

        $this->assertDatabaseHas('exercises', [
            'group_id' => $group->id,
            'name' => 'Pull-Ups',
            'measurement_type' => Exercise::MEASUREMENT_REPS_ONLY,
            'created_by_user_id' => $member->id,
        ]);
    }

    public function test_member_can_edit_their_own_exercise(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $member->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create([
            'created_by_user_id' => $member->id,
            'name' => 'Pull-Ups',
        ]);

        $this->actingAs($member)
            ->patch(route('groups.exercises.update', [$group, $exercise]), [
                'name' => 'Pull-Ups (wide grip)',
                'description' => 'Wider grip variant.',
            ])
            ->assertRedirect(route('groups.exercises.index', $group));

        $this->assertSame('Pull-Ups (wide grip)', $exercise->fresh()->name);
    }

    public function test_member_cannot_edit_others_exercise(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $member->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create([
            'created_by_user_id' => $owner->id,
        ]);

        $this->actingAs($member)
            ->patch(route('groups.exercises.update', [$group, $exercise]), [
                'name' => 'Hacked',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_edit_any_exercise(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $author = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $author->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create([
            'created_by_user_id' => $author->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('groups.exercises.update', [$group, $exercise]), [
                'name' => 'Renamed',
            ])
            ->assertRedirect();

        $this->assertSame('Renamed', $exercise->fresh()->name);
    }

    public function test_admin_can_soft_delete_exercise(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();

        $this->actingAs($admin)
            ->delete(route('groups.exercises.destroy', [$group, $exercise]))
            ->assertRedirect(route('groups.exercises.index', $group));

        $this->assertSoftDeleted($exercise);
    }

    public function test_exercise_in_other_group_returns_404(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $otherGroup = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($otherGroup)->repsOnly()->create();

        $this->actingAs($owner)
            ->patch(route('groups.exercises.update', [$group, $exercise]), [
                'name' => 'X',
            ])
            ->assertNotFound();
    }
}
