<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Workout
 */
class WorkoutFeedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workout_date' => $this->workout_date,
            'notes' => $this->notes,
            'group_points_earned' => $this->group_points_earned !== null
                ? (float) $this->group_points_earned
                : 0.0,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name ?? 'Former member',
            ]),
            'exercises' => $this->workoutExercises->map(fn ($we) => [
                'id' => $we->exercise?->id,
                'name' => $we->exercise?->name ?? 'Removed exercise',
                'measurement_type' => $we->exercise?->measurement_type,
                'logs' => $we->workoutExerciseLogs->map(fn ($log) => [
                    'repetitions' => $log->repetitions !== null ? (int) $log->repetitions : null,
                    'exercise_metric' => $log->exercise_metric !== null ? (float) $log->exercise_metric : null,
                    'metric_unit_name' => $log->metricUnit?->name,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
