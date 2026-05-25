<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use App\Models\WorkoutExerciseLog;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:140'],
            'measurement_type' => ['nullable', Rule::in(Exercise::MEASUREMENT_TYPES)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $exercise = $this->route('exercise');

            if (! $exercise instanceof Exercise) {
                return;
            }

            $incoming = $this->input('measurement_type');
            if ($incoming === null || $incoming === $exercise->measurement_type) {
                return;
            }

            $hasLogs = WorkoutExerciseLog::query()
                ->whereHas('workoutExercise', fn ($q) => $q->where('exercise_id', $exercise->id))
                ->exists();

            if ($hasLogs) {
                $validator->errors()->add(
                    'measurement_type',
                    'Cannot change measurement type after the exercise has been logged.'
                );
            }
        });
    }
}
