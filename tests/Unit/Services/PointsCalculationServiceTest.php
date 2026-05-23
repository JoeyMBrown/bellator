<?php

namespace Tests\Unit\Services;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupExercisePoints;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseLog;
use App\Services\PointsCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PointsCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PointsCalculationService $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new PointsCalculationService;
    }

    protected function makeLog(string $type, ?int $reps, ?float $metric): WorkoutExerciseLog
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $exercise = Exercise::factory()->forGroup($group)->create([
            'measurement_type' => $type,
            'created_by_user_id' => $owner->id,
        ]);
        $workout = Workout::factory()->forUser($owner)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        return WorkoutExerciseLog::factory()->create([
            'workout_exercise_id' => $workoutExercise->id,
            'repetitions' => $reps,
            'exercise_metric' => $metric,
        ]);
    }

    public function test_reps_only_calculation(): void
    {
        $log = $this->makeLog(Exercise::MEASUREMENT_REPS_ONLY, 20, null);
        $points = GroupExercisePoints::factory()->make([
            'points_per_unit' => 0.5,
        ]);

        $this->assertEqualsWithDelta(10.0, $this->calc->calculate($log, $points), 0.0001);
    }

    public function test_weighted_reps_calculation(): void
    {
        $log = $this->makeLog(Exercise::MEASUREMENT_WEIGHTED_REPS, 5, 100.0);
        $points = GroupExercisePoints::factory()->make([
            'points_per_unit' => 0.02,
        ]);

        // 5 * 100 * 0.02 = 10.0
        $this->assertEqualsWithDelta(10.0, $this->calc->calculate($log, $points), 0.0001);
    }

    public function test_distance_calculation(): void
    {
        $log = $this->makeLog(Exercise::MEASUREMENT_DISTANCE, 1, 3.5);
        $points = GroupExercisePoints::factory()->make([
            'points_per_unit' => 5.0,
        ]);

        // 3.5 * 5 = 17.5
        $this->assertEqualsWithDelta(17.5, $this->calc->calculate($log, $points), 0.0001);
    }

    public function test_duration_calculation(): void
    {
        $log = $this->makeLog(Exercise::MEASUREMENT_DURATION, 1, 60.0);
        $points = GroupExercisePoints::factory()->make([
            'points_per_unit' => 0.1,
        ]);

        // 60 * 0.1 = 6.0
        $this->assertEqualsWithDelta(6.0, $this->calc->calculate($log, $points), 0.0001);
    }

    public function test_anchor_reps_only(): void
    {
        $ppu = $this->calc->deriveFromAnchor(Exercise::MEASUREMENT_REPS_ONLY, [
            'points' => 10,
            'reps' => 20,
        ]);
        $this->assertEqualsWithDelta(0.5, $ppu, 0.0001);
    }

    public function test_anchor_weighted_reps(): void
    {
        $ppu = $this->calc->deriveFromAnchor(Exercise::MEASUREMENT_WEIGHTED_REPS, [
            'points' => 10,
            'reps' => 5,
            'weight' => 100,
        ]);
        $this->assertEqualsWithDelta(0.02, $ppu, 0.0001);
    }

    public function test_anchor_distance(): void
    {
        $ppu = $this->calc->deriveFromAnchor(Exercise::MEASUREMENT_DISTANCE, [
            'points' => 5,
            'distance' => 1,
        ]);
        $this->assertEqualsWithDelta(5.0, $ppu, 0.0001);
    }

    public function test_anchor_duration(): void
    {
        $ppu = $this->calc->deriveFromAnchor(Exercise::MEASUREMENT_DURATION, [
            'points' => 6,
            'seconds' => 60,
        ]);
        $this->assertEqualsWithDelta(0.1, $ppu, 0.0001);
    }

    public function test_anchor_zero_denominator_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calc->deriveFromAnchor(Exercise::MEASUREMENT_DISTANCE, [
            'points' => 5,
            'distance' => 0,
        ]);
    }
}
