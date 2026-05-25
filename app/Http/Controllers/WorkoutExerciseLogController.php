<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutExerciseLogRequest;
use App\Http\Requests\UpdateWorkoutExerciseLogRequest;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseLog;
use App\Services\WorkoutExerciseLogService;
use Illuminate\Http\RedirectResponse;

class WorkoutExerciseLogController extends Controller
{
    public function __construct(protected WorkoutExerciseLogService $logs) {}

    public function store(
        StoreWorkoutExerciseLogRequest $request,
        Workout $workout,
        WorkoutExercise $workoutExercise,
    ): RedirectResponse {
        $this->authorize('update', $workout);
        abort_if($workoutExercise->workout_id !== $workout->id, 404);

        $this->logs->create($workoutExercise, $request->validated());

        return redirect()
            ->route('workout.exercise.show', [$workout, $workoutExercise])
            ->with('success', 'Set logged.');
    }

    public function update(
        UpdateWorkoutExerciseLogRequest $request,
        Workout $workout,
        WorkoutExercise $workoutExercise,
        WorkoutExerciseLog $log,
    ): RedirectResponse {
        $this->authorize('update', $log);
        abort_if($workoutExercise->workout_id !== $workout->id, 404);
        abort_if($log->workout_exercise_id !== $workoutExercise->id, 404);

        $this->logs->update($log, $request->validated());

        return redirect()
            ->route('workout.exercise.show', [$workout, $workoutExercise])
            ->with('success', 'Set updated.');
    }

    public function destroy(
        Workout $workout,
        WorkoutExercise $workoutExercise,
        WorkoutExerciseLog $log,
    ): RedirectResponse {
        $this->authorize('delete', $log);
        abort_if($workoutExercise->workout_id !== $workout->id, 404);
        abort_if($log->workout_exercise_id !== $workoutExercise->id, 404);

        $this->logs->delete($log);

        return redirect()
            ->route('workout.exercise.show', [$workout, $workoutExercise])
            ->with('success', 'Set removed.');
    }
}
