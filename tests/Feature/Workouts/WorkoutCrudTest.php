<?php

namespace Tests\Feature\Workouts;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_workout_create_form(): void
    {
        $user = User::factory()->create();
        Group::factory()->withOwner($user)->create();

        $this->actingAs($user)
            ->get(route('workout.create'))
            ->assertOk();
    }

    public function test_zero_group_user_cannot_view_workout_create_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('workout.create'))
            ->assertRedirect(route('onboarding'));
    }

    public function test_user_can_create_workout_tagged_to_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();

        $response = $this->actingAs($user)->post(route('workout.store'), [
            'workout_date' => '2026-05-23',
            'notes' => 'Felt strong.',
            'group_ids' => [$group->id],
        ]);

        $workout = Workout::query()->where('user_id', $user->id)->latest('id')->first();

        $this->assertNotNull($workout);
        $response->assertRedirect(route('workout.show', $workout));
        $this->assertSame('Felt strong.', $workout->notes);
        $this->assertTrue($workout->groups()->where('groups.id', $group->id)->exists());
    }

    public function test_workout_creation_requires_at_least_one_group(): void
    {
        $user = User::factory()->create();
        Group::factory()->withOwner($user)->create();

        $this->actingAs($user)
            ->post(route('workout.store'), [
                'workout_date' => '2026-05-23',
                'group_ids' => [],
            ])
            ->assertSessionHasErrors('group_ids');
    }

    public function test_user_cannot_tag_workout_to_group_they_do_not_belong_to(): void
    {
        $user = User::factory()->create();
        $myGroup = Group::factory()->withOwner($user)->create();
        $otherGroup = Group::factory()->withOwner(User::factory()->create())->create();

        $this->actingAs($user)
            ->post(route('workout.store'), [
                'workout_date' => '2026-05-23',
                'group_ids' => [$myGroup->id, $otherGroup->id],
            ])
            ->assertSessionHasErrors('group_ids');
    }

    public function test_owner_can_update_their_workout(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();

        $this->actingAs($user)
            ->patch(route('workout.update', $workout), [
                'workout_date' => '2026-05-24',
                'notes' => 'Updated.',
                'group_ids' => [$group->id],
            ])
            ->assertRedirect(route('workout.show', $workout));

        $this->assertSame('Updated.', $workout->fresh()->notes);
    }

    public function test_non_owner_cannot_update_workout(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $other->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);
        $workout = Workout::factory()->forUser($owner)->forGroup($group)->create();

        $this->actingAs($other)
            ->patch(route('workout.update', $workout), [
                'workout_date' => '2026-05-24',
                'group_ids' => [$group->id],
            ])
            ->assertForbidden();
    }

    public function test_owner_can_delete_workout(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();

        $this->actingAs($user)
            ->delete(route('workout.destroy', $workout))
            ->assertRedirect(route('workout.index'));

        $this->assertSoftDeleted($workout);
    }

    public function test_group_member_can_view_workout_in_their_group(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $viewer->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);
        $workout = Workout::factory()->forUser($owner)->forGroup($group)->create();

        $this->actingAs($viewer)
            ->get(route('workout.show', $workout))
            ->assertOk();
    }

    public function test_stranger_cannot_view_workout(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        Group::factory()->withOwner($stranger)->create(); // pass onboarding gate
        $workout = Workout::factory()->forUser($owner)->forGroup($group)->create();

        $this->actingAs($stranger)
            ->get(route('workout.show', $workout))
            ->assertForbidden();
    }
}
