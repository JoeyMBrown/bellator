<?php

namespace Tests\Unit\Services;

use App\Jobs\BackfillGroupExerciseLogPointsJob;
use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupExercisePoints;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseLog;
use App\Models\WorkoutExerciseLogPoints;
use App\Services\GroupExercisePointsService;
use App\Services\PointsCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GroupExercisePointsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GroupExercisePointsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GroupExercisePointsService(new PointsCalculationService);
    }

    public function test_setting_points_for_the_first_time_inserts_active_row(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();

        $this->service->setPoints($group, $exercise, 0.5);

        $this->assertDatabaseHas('group_exercise_points', [
            'group_id' => $group->id,
            'exercise_id' => $exercise->id,
            'end_date' => null,
        ]);
    }

    public function test_setting_points_first_time_dispatches_backfill(): void
    {
        Queue::fake();
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();

        $this->service->setPoints($group, $exercise, 0.5);

        Queue::assertPushed(BackfillGroupExerciseLogPointsJob::class);
    }

    public function test_subsequent_edit_does_not_dispatch_backfill(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();

        $this->service->setPoints($group, $exercise, 0.5);

        Queue::fake();

        $this->service->setPoints($group, $exercise, 1.0);

        Queue::assertNotPushed(BackfillGroupExerciseLogPointsJob::class);
    }

    public function test_subsequent_edit_closes_prior_row(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();

        $this->service->setPoints($group, $exercise, 0.5);
        $this->service->setPoints($group, $exercise, 1.0);

        $active = GroupExercisePoints::query()
            ->where('group_id', $group->id)
            ->where('exercise_id', $exercise->id)
            ->whereNull('end_date')
            ->get();

        $this->assertCount(1, $active);
        $this->assertEqualsWithDelta(1.0, (float) $active->first()->points_per_unit, 0.0001);

        $closed = GroupExercisePoints::query()
            ->where('group_id', $group->id)
            ->where('exercise_id', $exercise->id)
            ->whereNotNull('end_date')
            ->get();
        $this->assertCount(1, $closed);
    }

    public function test_snapshot_at_log_time_is_not_changed_by_later_edits(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create([
            'created_by_user_id' => $owner->id,
        ]);
        $workout = Workout::factory()->forUser($owner)->forGroup($group)->create();
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

        // First-time set, then simulate the snapshot at log time:
        $this->service->setPoints($group, $exercise, 0.5);
        $logPoints = WorkoutExerciseLogPoints::create([
            'workout_exercise_log_id' => $log->id,
            'group_id' => $group->id,
            'points_earned' => 10 * 0.5,
        ]);

        // Edit the rate; the existing log's recorded points should not change.
        $this->service->setPoints($group, $exercise, 1.0);

        $this->assertEqualsWithDelta(5.0, (float) $logPoints->fresh()->points_earned, 0.0001);
    }

    public function test_backfill_fills_null_log_points(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create([
            'created_by_user_id' => $owner->id,
        ]);
        $workout = Workout::factory()->forUser($owner)->forGroup($group)->create();
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
        $logPoints = WorkoutExerciseLogPoints::create([
            'workout_exercise_log_id' => $log->id,
            'group_id' => $group->id,
            'points_earned' => null,
        ]);

        // First-time set runs the backfill synchronously (queue driver = sync in tests).
        $this->service->setPoints($group, $exercise, 0.5);

        $this->assertEqualsWithDelta(5.0, (float) $logPoints->fresh()->points_earned, 0.0001);
    }
}
