<?php

namespace Tests\Feature\Workouts;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\MetricUnit;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseLog;
use App\Models\WorkoutExerciseLogPoints;
use App\Services\GroupExercisePointsService;
use App\Services\PointsCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutExerciseLogTest extends TestCase
{
    use RefreshDatabase;

    protected function pointsService(): GroupExercisePointsService
    {
        return new GroupExercisePointsService(new PointsCalculationService);
    }

    public function test_owner_can_add_exercise_from_tagged_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();

        $this->actingAs($user)
            ->post(route('workout.exercise.store', $workout), [
                'exercise_id' => $exercise->id,
            ])
            ->assertRedirect(route('workout.show', $workout));

        $this->assertDatabaseHas('workout_exercises', [
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);
    }

    public function test_owner_cannot_add_exercise_from_untagged_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $otherGroup = Group::factory()->withOwner($user)->create();
        $exercise = Exercise::factory()->forGroup($otherGroup)->repsOnly()->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();

        $this->actingAs($user)
            ->post(route('workout.exercise.store', $workout), [
                'exercise_id' => $exercise->id,
            ])
            ->assertSessionHasErrors('exercise_id');
    }

    public function test_log_save_snapshots_points_for_each_tagged_group(): void
    {
        $user = User::factory()->create();
        $group1 = Group::factory()->withOwner($user)->create();
        $group2 = Group::factory()->withOwner($user)->create();

        // Same-named exercise per group; tag the workout to both groups but
        // attach exercise from group1 only (cross-group dedup is out of scope).
        $exercise = Exercise::factory()->forGroup($group1)->repsOnly()->create();
        $exerciseInG2 = Exercise::factory()->forGroup($group2)->repsOnly()->create([
            'name' => $exercise->name,
        ]);

        $this->pointsService()->setPoints($group1, $exercise, 0.5);
        $this->pointsService()->setPoints($group2, $exerciseInG2, 1.0);

        $workout = Workout::factory()->forUser($user)->create();
        $workout->groups()->sync([$group1->id, $group2->id]);
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->actingAs($user)
            ->post(route('workout.exercise.log.store', [$workout, $workoutExercise]), [
                'repetitions' => 10,
            ])
            ->assertRedirect();

        $log = WorkoutExerciseLog::query()
            ->where('workout_exercise_id', $workoutExercise->id)
            ->latest('id')
            ->firstOrFail();

        $g1Points = WorkoutExerciseLogPoints::query()
            ->where('workout_exercise_log_id', $log->id)
            ->where('group_id', $group1->id)
            ->firstOrFail();

        $g2Points = WorkoutExerciseLogPoints::query()
            ->where('workout_exercise_log_id', $log->id)
            ->where('group_id', $group2->id)
            ->firstOrFail();

        $this->assertEqualsWithDelta(5.0, (float) $g1Points->points_earned, 0.0001);
        // Group 2 has a different exercise (not the one referenced by the log) so its
        // active rate doesn't apply; points_earned must be NULL for group 2.
        $this->assertNull($g2Points->points_earned);
    }

    public function test_log_save_writes_null_when_no_active_rate(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->actingAs($user)
            ->post(route('workout.exercise.log.store', [$workout, $workoutExercise]), [
                'repetitions' => 10,
            ])
            ->assertRedirect();

        $log = WorkoutExerciseLog::query()
            ->where('workout_exercise_id', $workoutExercise->id)
            ->latest('id')
            ->firstOrFail();

        $points = WorkoutExerciseLogPoints::query()
            ->where('workout_exercise_log_id', $log->id)
            ->where('group_id', $group->id)
            ->firstOrFail();

        $this->assertNull($points->points_earned);
    }

    public function test_first_time_rubric_set_backfills_existing_null_log_points(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->actingAs($user)
            ->post(route('workout.exercise.log.store', [$workout, $workoutExercise]), [
                'repetitions' => 10,
            ]);

        $log = WorkoutExerciseLog::query()
            ->where('workout_exercise_id', $workoutExercise->id)
            ->latest('id')
            ->firstOrFail();

        $this->pointsService()->setPoints($group, $exercise, 0.5);

        $points = WorkoutExerciseLogPoints::query()
            ->where('workout_exercise_log_id', $log->id)
            ->where('group_id', $group->id)
            ->firstOrFail();

        $this->assertEqualsWithDelta(5.0, (float) $points->points_earned, 0.0001);
    }

    public function test_log_validation_requires_metric_for_weighted_reps(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $exercise = Exercise::factory()->forGroup($group)->weightedReps()->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->actingAs($user)
            ->post(route('workout.exercise.log.store', [$workout, $workoutExercise]), [
                'repetitions' => 5,
            ])
            ->assertSessionHasErrors(['exercise_metric', 'metric_unit_id']);
    }

    public function test_non_owner_cannot_log(): void
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
        $workout = Workout::factory()->forUser($owner)->forGroup($group)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->actingAs($admin)
            ->post(route('workout.exercise.log.store', [$workout, $workoutExercise]), [
                'repetitions' => 10,
            ])
            ->assertForbidden();
    }

    public function test_owner_can_delete_log(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);
        $log = WorkoutExerciseLog::factory()->create([
            'workout_exercise_id' => $workoutExercise->id,
            'repetitions' => 10,
            'exercise_metric' => null,
            'metric_unit_id' => null,
        ]);

        $this->actingAs($user)
            ->delete(route('workout.exercise.log.destroy', [$workout, $workoutExercise, $log]))
            ->assertRedirect();

        $this->assertSoftDeleted($log);
    }

    public function test_distance_log_requires_metric_unit_id(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $exercise = Exercise::factory()->forGroup($group)->distance()->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        // Has distance, missing unit
        $this->actingAs($user)
            ->post(route('workout.exercise.log.store', [$workout, $workoutExercise]), [
                'exercise_metric' => 1.5,
            ])
            ->assertSessionHasErrors('metric_unit_id');
    }

    public function test_duration_log_does_not_require_unit(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $exercise = Exercise::factory()->forGroup($group)->duration()->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->actingAs($user)
            ->post(route('workout.exercise.log.store', [$workout, $workoutExercise]), [
                'exercise_metric' => 60,
            ])
            ->assertRedirect();
    }
}
