<?php

namespace App\Services;

use App\Jobs\BackfillGroupExerciseLogPointsJob;
use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupExercisePoints;
use App\Support\PresetRubrics;
use Illuminate\Support\Facades\DB;

class GroupExercisePointsService
{
    public function __construct(protected PointsCalculationService $calculator) {}

    /**
     * Persist a new points_per_unit row for (group, exercise), closing any
     * existing active row. Dispatches a backfill job if this is the first
     * time the rate has ever been set for that pair.
     */
    public function setPoints(Group $group, Exercise $exercise, float $pointsPerUnit): GroupExercisePoints
    {
        return DB::transaction(function () use ($group, $exercise, $pointsPerUnit) {
            $hasPriorRow = GroupExercisePoints::query()
                ->where('group_id', $group->id)
                ->where('exercise_id', $exercise->id)
                ->withTrashed()
                ->exists();

            GroupExercisePoints::query()
                ->where('group_id', $group->id)
                ->where('exercise_id', $exercise->id)
                ->whereNull('end_date')
                ->update(['end_date' => now()]);

            $row = GroupExercisePoints::create([
                'group_id' => $group->id,
                'exercise_id' => $exercise->id,
                'points_per_unit' => $pointsPerUnit,
                'start_date' => now(),
                'end_date' => null,
            ]);

            if (! $hasPriorRow) {
                BackfillGroupExerciseLogPointsJob::dispatch($group->id, $exercise->id);
            }

            return $row;
        });
    }

    /**
     * Apply a named preset rubric to a freshly-seeded group's default
     * exercises (mapped by name).
     */
    public function applyPreset(Group $group, string $preset): void
    {
        $values = PresetRubrics::pointsByExerciseName($preset);
        if ($values === []) {
            return;
        }

        $exercises = Exercise::query()
            ->where('group_id', $group->id)
            ->whereIn('name', array_keys($values))
            ->get()
            ->keyBy('name');

        foreach ($values as $name => $ppu) {
            $exercise = $exercises->get($name);
            if ($exercise === null) {
                continue;
            }

            $this->setPoints($group, $exercise, (float) $ppu);
        }
    }
}
