<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use Illuminate\Foundation\Http\FormRequest;

class StoreGroupExercisePointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $exercise = $this->route('exercise');
        $type = $exercise instanceof Exercise ? $exercise->measurement_type : null;

        $base = [
            'points' => ['required', 'numeric', 'gt:0'],
        ];

        return match ($type) {
            Exercise::MEASUREMENT_REPS_ONLY => array_merge($base, [
                'reps' => ['required', 'integer', 'min:1'],
            ]),
            Exercise::MEASUREMENT_WEIGHTED_REPS => array_merge($base, [
                'reps' => ['required', 'integer', 'min:1'],
                'weight' => ['required', 'numeric', 'gt:0'],
            ]),
            Exercise::MEASUREMENT_DISTANCE => array_merge($base, [
                'distance' => ['required', 'numeric', 'gt:0'],
            ]),
            Exercise::MEASUREMENT_DURATION => array_merge($base, [
                'seconds' => ['required', 'numeric', 'gt:0'],
            ]),
            default => $base,
        };
    }
}
