<?php

namespace App\Jobs;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupExercisePoints;
use App\Models\WorkoutExerciseLogPoints;
use App\Services\PointsCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BackfillGroupExerciseLogPointsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $groupId,
        public int $exerciseId,
    ) {}

    public function handle(PointsCalculationService $calc): void
    {
        $group = Group::find($this->groupId);
        $exercise = Exercise::find($this->exerciseId);

        if ($group === null || $exercise === null) {
            return;
        }

        $points = GroupExercisePoints::query()
            ->where('group_id', $group->id)
            ->where('exercise_id', $exercise->id)
            ->whereNull('end_date')
            ->first();

        if ($points === null) {
            return;
        }

        WorkoutExerciseLogPoints::query()
            ->whereNull('points_earned')
            ->where('group_id', $group->id)
            ->whereHas('workoutExerciseLog.workoutExercise', function ($query) use ($exercise) {
                $query->where('exercise_id', $exercise->id);
            })
            ->with('workoutExerciseLog.workoutExercise.exercise')
            ->chunkById(200, function ($rows) use ($calc, $points) {
                foreach ($rows as $row) {
                    $log = $row->workoutExerciseLog;
                    if ($log === null) {
                        continue;
                    }

                    $row->points_earned = $calc->calculate($log, $points);
                    $row->save();
                }
            });
    }
}
