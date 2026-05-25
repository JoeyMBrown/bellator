<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\GroupExercisePoints;
use App\Models\WorkoutExerciseLog;
use InvalidArgumentException;

class PointsCalculationService
{
    /**
     * Calculate points_earned for a log given an active group_exercise_points row.
     */
    public function calculate(WorkoutExerciseLog $log, GroupExercisePoints $points): float
    {
        $exercise = $log->workoutExercise?->exercise;

        if ($exercise === null) {
            throw new InvalidArgumentException('Log has no exercise.');
        }

        $ppu = (float) $points->points_per_unit;
        $reps = (int) ($log->repetitions ?? 1);
        $metric = (float) ($log->exercise_metric ?? 0);

        return match ($exercise->measurement_type) {
            Exercise::MEASUREMENT_REPS_ONLY => $reps * $ppu,
            Exercise::MEASUREMENT_WEIGHTED_REPS => $reps * $metric * $ppu,
            Exercise::MEASUREMENT_DISTANCE => $metric * $ppu,
            Exercise::MEASUREMENT_DURATION => $metric * $ppu,
            default => throw new InvalidArgumentException(
                "Unsupported measurement_type: {$exercise->measurement_type}"
            ),
        };
    }

    /**
     * Derive points_per_unit from an anchor-based input.
     *
     * Expected $anchor keys per measurement_type:
     * - reps_only: points, reps
     * - weighted_reps: points, reps, weight
     * - distance: points, distance
     * - duration: points, seconds
     */
    public function deriveFromAnchor(string $measurementType, array $anchor): float
    {
        $points = (float) ($anchor['points'] ?? 0);

        return match ($measurementType) {
            Exercise::MEASUREMENT_REPS_ONLY => $this->safeDivide(
                $points,
                (float) ($anchor['reps'] ?? 0)
            ),
            Exercise::MEASUREMENT_WEIGHTED_REPS => $this->safeDivide(
                $points,
                ((float) ($anchor['reps'] ?? 0)) * ((float) ($anchor['weight'] ?? 0))
            ),
            Exercise::MEASUREMENT_DISTANCE => $this->safeDivide(
                $points,
                (float) ($anchor['distance'] ?? 0)
            ),
            Exercise::MEASUREMENT_DURATION => $this->safeDivide(
                $points,
                (float) ($anchor['seconds'] ?? 0)
            ),
            default => throw new InvalidArgumentException(
                "Unsupported measurement_type: {$measurementType}"
            ),
        };
    }

    protected function safeDivide(float $numerator, float $denominator): float
    {
        if ($denominator == 0.0) {
            throw new InvalidArgumentException('Anchor denominator cannot be zero.');
        }

        return $numerator / $denominator;
    }
}
