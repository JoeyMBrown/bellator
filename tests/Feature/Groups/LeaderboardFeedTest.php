<?php

namespace Tests\Feature\Groups;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseLog;
use App\Services\GroupExercisePointsService;
use App\Services\PointsCalculationService;
use App\Services\WorkoutExerciseLogService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function pointsService(): GroupExercisePointsService
    {
        return new GroupExercisePointsService(new PointsCalculationService);
    }

    protected function logService(): WorkoutExerciseLogService
    {
        return new WorkoutExerciseLogService(new PointsCalculationService);
    }

    protected function logWorkout(User $user, Group $group, Exercise $exercise, Carbon $when, int $reps): Workout
    {
        $workout = Workout::factory()->forUser($user)->create([
            'workout_date' => $when,
        ]);
        $workout->groups()->sync([$group->id]);
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->logService()->create($workoutExercise, [
            'repetitions' => $reps,
            'exercise_metric' => null,
            'metric_unit_id' => null,
        ]);

        return $workout->fresh();
    }

    public function test_leaderboard_ranks_by_total_points_descending(): void
    {
        $owner = User::factory()->create();
        $member1 = User::factory()->create(['name' => 'Alpha']);
        $member2 = User::factory()->create(['name' => 'Bravo']);
        $group = Group::factory()->withOwner($owner)->create(['timezone' => 'UTC']);
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $member1->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $member2->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();
        $this->pointsService()->setPoints($group, $exercise, 1.0);

        $now = Carbon::now('UTC');
        $this->logWorkout($member1, $group, $exercise, $now->copy(), 10);
        $this->logWorkout($member2, $group, $exercise, $now->copy(), 25);

        $this->actingAs($owner)
            ->get(route('groups.show', $group))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('leaderboard.window', 'week')
                ->where('leaderboard.rows.0.user_id', $member2->id)
                ->where('leaderboard.rows.0.total_points', 25)
                ->where('leaderboard.rows.0.rank', 1)
                ->where('leaderboard.rows.1.user_id', $member1->id)
                ->where('leaderboard.rows.1.total_points', 10)
                ->where('leaderboard.rows.1.rank', 2)
            );
    }

    public function test_leaderboard_excludes_workouts_outside_window(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create(['timezone' => 'UTC']);
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();
        $this->pointsService()->setPoints($group, $exercise, 1.0);

        // 60 days ago — outside week + month windows
        $this->logWorkout($owner, $group, $exercise, Carbon::now('UTC')->subDays(60), 50);
        // Today — inside all windows
        $this->logWorkout($owner, $group, $exercise, Carbon::now('UTC'), 5);

        $this->actingAs($owner)
            ->get(route('groups.show', [$group, 'window' => 'week']))
            ->assertInertia(fn ($page) => $page
                ->where('leaderboard.rows.0.total_points', 5)
            );

        $this->actingAs($owner)
            ->get(route('groups.show', [$group, 'window' => 'all']))
            ->assertInertia(fn ($page) => $page
                ->where('leaderboard.rows.0.total_points', 55)
            );
    }

    public function test_leaderboard_only_counts_points_for_this_group(): void
    {
        $owner = User::factory()->create();
        $group1 = Group::factory()->withOwner($owner)->create();
        $group2 = Group::factory()->withOwner($owner)->create();

        $exercise1 = Exercise::factory()->forGroup($group1)->repsOnly()->create();
        $exercise2 = Exercise::factory()->forGroup($group2)->repsOnly()->create();
        $this->pointsService()->setPoints($group1, $exercise1, 1.0);
        $this->pointsService()->setPoints($group2, $exercise2, 10.0);

        // Workout tagged to both groups but with exercise from group1 only
        $workout = Workout::factory()->forUser($owner)->create();
        $workout->groups()->sync([$group1->id, $group2->id]);
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise1->id,
        ]);
        $this->logService()->create($workoutExercise, [
            'repetitions' => 10,
        ]);

        $this->actingAs($owner)
            ->get(route('groups.show', $group1))
            ->assertInertia(fn ($page) => $page
                ->where('leaderboard.rows.0.total_points', 10)
            );

        $this->actingAs($owner)
            ->get(route('groups.show', $group2))
            ->assertInertia(fn ($page) => $page
                ->where('leaderboard.rows.0.total_points', 0)
            );
    }

    public function test_activity_feed_returns_workouts_for_group_in_reverse_chronological_order(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();

        $oldWorkout = $this->logWorkout($owner, $group, $exercise, Carbon::now('UTC')->subDay(), 5);
        $newWorkout = $this->logWorkout($owner, $group, $exercise, Carbon::now('UTC'), 10);

        $this->actingAs($owner)
            ->get(route('groups.show', $group))
            ->assertInertia(fn ($page) => $page
                ->where('feed.data.0.id', $newWorkout->id)
                ->where('feed.data.1.id', $oldWorkout->id)
            );
    }

    public function test_activity_feed_paginates_at_20_per_page(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();

        for ($i = 0; $i < 25; $i++) {
            $this->logWorkout($owner, $group, $exercise, Carbon::now('UTC')->subMinutes($i), 1);
        }

        $this->actingAs($owner)
            ->get(route('groups.show', $group))
            ->assertInertia(fn ($page) => $page
                ->where('feed.meta.per_page', 20)
                ->where('feed.meta.total', 25)
                ->where('feed.meta.has_more_pages', true)
            );

        $this->actingAs($owner)
            ->get(route('groups.show', [$group, 'feed_page' => 2]))
            ->assertInertia(fn ($page) => $page
                ->where('feed.meta.current_page', 2)
                ->where('feed.meta.has_more_pages', false)
            );
    }

    public function test_member_profile_page_shows_stats(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();
        $this->pointsService()->setPoints($group, $exercise, 1.0);

        $this->logWorkout($owner, $group, $exercise, Carbon::now('UTC'), 10);

        $this->actingAs($owner)
            ->get(route('groups.members.show', [$group, $owner]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Groups/Members/Show')
                ->where('profile.user_id', $owner->id)
                ->where('profile.role', 'owner')
                ->where('aggregate.total_points', 10)
                ->where('aggregate.workout_count', 1)
            );
    }

    public function test_member_profile_404_for_non_member_target(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();

        $this->actingAs($owner)
            ->get(route('groups.members.show', [$group, $stranger]))
            ->assertNotFound();
    }

    public function test_non_member_cannot_view_leaderboard_or_feed(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        Group::factory()->withOwner($stranger)->create();

        $this->actingAs($stranger)
            ->get(route('groups.show', $group))
            ->assertForbidden();
    }
}
