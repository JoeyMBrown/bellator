<?php

namespace Tests\Feature\Groups;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupExercisePoints;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupRubricTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_view_rubric(): void
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
            ->get(route('groups.rubric.index', $group))
            ->assertOk();
    }

    public function test_non_member_cannot_view_rubric(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        Group::factory()->withOwner($stranger)->create();

        $this->actingAs($stranger)
            ->get(route('groups.rubric.index', $group))
            ->assertForbidden();
    }

    public function test_admin_can_set_points_via_anchor_for_weighted_reps(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);
        $exercise = Exercise::factory()->forGroup($group)->weightedReps()->create();

        $this->actingAs($admin)
            ->put(route('groups.rubric.store', [$group, $exercise]), [
                'points' => 10,
                'reps' => 5,
                'weight' => 100,
            ])
            ->assertRedirect(route('groups.rubric.index', $group));

        $row = GroupExercisePoints::query()
            ->where('group_id', $group->id)
            ->where('exercise_id', $exercise->id)
            ->whereNull('end_date')
            ->firstOrFail();

        $this->assertEqualsWithDelta(0.02, (float) $row->points_per_unit, 0.0001);
    }

    public function test_member_cannot_set_points(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $member->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();

        $this->actingAs($member)
            ->put(route('groups.rubric.store', [$group, $exercise]), [
                'points' => 5,
                'reps' => 10,
            ])
            ->assertForbidden();
    }

    public function test_distance_anchor_validation_requires_distance(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->distance()->create();

        $this->actingAs($owner)
            ->put(route('groups.rubric.store', [$group, $exercise]), [
                'points' => 5,
            ])
            ->assertSessionHasErrors('distance');
    }

    public function test_creating_group_with_preset_seeds_default_exercises_and_points(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('groups.store'), [
                'name' => 'My Crew',
                'timezone' => 'UTC',
                'preset_rubric' => 'strength',
            ])
            ->assertRedirect();

        $group = Group::where('name', 'My Crew')->firstOrFail();

        // Default exercises are seeded
        $this->assertGreaterThan(0, $group->exercises()->count());

        // At least one exercise has points configured
        $this->assertGreaterThan(
            0,
            GroupExercisePoints::where('group_id', $group->id)
                ->whereNull('end_date')
                ->count()
        );
    }
}
