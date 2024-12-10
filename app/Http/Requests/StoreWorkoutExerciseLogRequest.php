<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutExerciseLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array //TODO: Flesh out rules, consider id naming convention here?
    {
        return [
            'repitions' => ['required'],
            'exercise_metric' => ['required'],
            'workout_exercise_id' => ['nullable'],
            'metric_unit_id' => ['nullable'],
        ];
    }
}
