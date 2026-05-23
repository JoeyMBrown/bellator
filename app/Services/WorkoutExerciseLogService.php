<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\GroupExercisePoints;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseLog;
use App\Models\WorkoutExerciseLogPoints;
use Illuminate\Support\Facades\DB;

class WorkoutExerciseLogService
{
    public function __construct(protected PointsCalculationService $calculator) {}

    public function create(WorkoutExercise $workoutExercise, array $data): WorkoutExerciseLog
    {
        return DB::transaction(function () use ($workoutExercise, $data) {
            $log = WorkoutExerciseLog::create([
                'workout_exercise_id' => $workoutExercise->id,
                'repetitions' => $data['repetitions'] ?? 1,
                'exercise_metric' => $data['exercise_metric'] ?? null,
                'metric_unit_id' => $data['metric_unit_id'] ?? null,
            ]);

            $this->syncPointsForLog($log->fresh(['workoutExercise.exercise', 'workoutExercise.workout.groups']));

            return $log->fresh(['workoutExercise.exercise']);
        });
    }

    public function update(WorkoutExerciseLog $log, array $data): WorkoutExerciseLog
    {
        return DB::transaction(function () use ($log, $data) {
            $log->fill([
                'repetitions' => $data['repetitions'] ?? $log->repetitions,
                'exercise_metric' => array_key_exists('exercise_metric', $data)
                    ? $data['exercise_metric']
                    : $log->exercise_metric,
                'metric_unit_id' => array_key_exists('metric_unit_id', $data)
                    ? $data['metric_unit_id']
                    : $log->metric_unit_id,
            ])->save();

            $this->syncPointsForLog($log->fresh(['workoutExercise.exercise', 'workoutExercise.workout.groups']));

            return $log->fresh(['workoutExercise.exercise']);
        });
    }

    public function delete(WorkoutExerciseLog $log): void
    {
        DB::transaction(function () use ($log) {
            $log->points()->delete();
            $log->delete();
        });
    }

    /**
     * Recompute all workout_exercise_log_points rows for every log in this
     * workout, against the workout's current set of tagged groups.
     */
    public function rebuildPointsForWorkout(Workout $workout): void
    {
        $logs = WorkoutExerciseLog::query()
            ->whereHas('workoutExercise', fn ($q) => $q->where('workout_id', $workout->id))
            ->with(['workoutExercise.exercise', 'workoutExercise.workout.groups'])
            ->get();

        foreach ($logs as $log) {
            $log->points()->delete();
            $this->syncPointsForLog($log);
        }
    }

    /**
     * Snapshot points for the given log against each currently-tagged group.
     */
    protected function syncPointsForLog(WorkoutExerciseLog $log): void
    {
        $exercise = $log->workoutExercise?->exercise;
        $workout = $log->workoutExercise?->workout;

        if ($exercise === null || $workout === null) {
            return;
        }

        $groupIds = $workout->groups->pluck('id');

        $activeRates = GroupExercisePoints::query()
            ->whereIn('group_id', $groupIds)
            ->where('exercise_id', $exercise->id)
            ->whereNull('end_date')
            ->get()
            ->keyBy('group_id');

        foreach ($groupIds as $groupId) {
            $rate = $activeRates->get($groupId);
            $pointsEarned = $rate !== null
                ? $this->calculator->calculate($log, $rate)
                : null;

            WorkoutExerciseLogPoints::updateOrCreate(
                ['workout_exercise_log_id' => $log->id, 'group_id' => $groupId],
                ['points_earned' => $pointsEarned],
            );
        }
    }
}
