<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutExerciseRequest;
use App\Models\MetricUnit;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Services\WorkoutExerciseService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WorkoutExerciseController extends Controller
{
    public function __construct(protected WorkoutExerciseService $workoutExercises) {}

    public function store(StoreWorkoutExerciseRequest $request, Workout $workout): RedirectResponse
    {
        $this->authorize('update', $workout);

        $this->workoutExercises->add($workout, (int) $request->validated()['exercise_id']);

        return redirect()
            ->route('workout.show', $workout)
            ->with('success', 'Exercise added.');
    }

    public function show(Workout $workout, WorkoutExercise $workoutExercise): Response
    {
        $this->authorize('view', $workout);
        abort_if($workoutExercise->workout_id !== $workout->id, 404);

        $workoutExercise->load(['exercise', 'workoutExerciseLogs.metricUnit']);

        return Inertia::render('Workouts/Exercises/Show', [
            'workout' => [
                'id' => $workout->id,
                'workout_date' => $workout->workout_date,
                'is_owner' => $workout->user_id === auth()->id(),
            ],
            'workoutExercise' => [
                'id' => $workoutExercise->id,
                'exercise' => [
                    'id' => $workoutExercise->exercise->id,
                    'name' => $workoutExercise->exercise->name,
                    'measurement_type' => $workoutExercise->exercise->measurement_type,
                ],
                'logs' => $workoutExercise->workoutExerciseLogs->map(fn ($log) => [
                    'id' => $log->id,
                    'repetitions' => $log->repetitions,
                    'exercise_metric' => $log->exercise_metric !== null ? (float) $log->exercise_metric : null,
                    'metric_unit_id' => $log->metric_unit_id,
                    'metric_unit_name' => $log->metricUnit?->name,
                ])->all(),
            ],
            'metricUnitOptions' => MetricUnit::query()->get(['id', 'name'])->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
            ]),
        ]);
    }

    public function destroy(Workout $workout, WorkoutExercise $workoutExercise): RedirectResponse
    {
        $this->authorize('update', $workout);
        abort_if($workoutExercise->workout_id !== $workout->id, 404);

        $this->workoutExercises->remove($workoutExercise);

        return redirect()
            ->route('workout.show', $workout)
            ->with('success', 'Exercise removed from workout.');
    }
}
