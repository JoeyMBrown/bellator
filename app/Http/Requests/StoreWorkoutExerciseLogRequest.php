<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use App\Models\WorkoutExercise;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutExerciseLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $workoutExercise = $this->route('workoutExercise');
        $type = $workoutExercise instanceof WorkoutExercise
            ? $workoutExercise->exercise?->measurement_type
            : null;

        return match ($type) {
            Exercise::MEASUREMENT_REPS_ONLY => [
                'repetitions' => ['required', 'integer', 'min:1'],
            ],
            Exercise::MEASUREMENT_WEIGHTED_REPS => [
                'repetitions' => ['required', 'integer', 'min:1'],
                'exercise_metric' => ['required', 'numeric', 'gt:0'],
                'metric_unit_id' => ['required', 'integer', 'exists:metric_units,id'],
            ],
            Exercise::MEASUREMENT_DISTANCE => [
                'exercise_metric' => ['required', 'numeric', 'gt:0'],
                'metric_unit_id' => ['required', 'integer', 'exists:metric_units,id'],
            ],
            Exercise::MEASUREMENT_DURATION => [
                'exercise_metric' => ['required', 'numeric', 'gt:0'],
                'metric_unit_id' => ['nullable', 'integer', 'exists:metric_units,id'],
            ],
            default => [
                'repetitions' => ['nullable', 'integer', 'min:1'],
                'exercise_metric' => ['nullable', 'numeric'],
                'metric_unit_id' => ['nullable', 'integer', 'exists:metric_units,id'],
            ],
        };
    }
}
