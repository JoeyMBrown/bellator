<?php

namespace App\Support;

class PresetRubrics
{
    public const STRENGTH = 'strength';

    public const ENDURANCE = 'endurance';

    public const BALANCED = 'balanced';

    public const ALL = [self::STRENGTH, self::ENDURANCE, self::BALANCED];

    /**
     * Return `points_per_unit` per default exercise name for the given preset.
     *
     * @return array<string, float>
     */
    public static function pointsByExerciseName(string $preset): array
    {
        return match ($preset) {
            self::STRENGTH => [
                'Bench Press' => 0.020,
                'Squat' => 0.020,
                'Push-Ups' => 1.0,
                'Plank' => 0.10,
                'Run' => 5.0,
                'Sprint' => 10.0,
                'Swim' => 8.0,
            ],
            self::ENDURANCE => [
                'Bench Press' => 0.010,
                'Squat' => 0.010,
                'Push-Ups' => 0.5,
                'Plank' => 0.20,
                'Run' => 15.0,
                'Sprint' => 25.0,
                'Swim' => 20.0,
            ],
            self::BALANCED => [
                'Bench Press' => 0.016,
                'Squat' => 0.016,
                'Push-Ups' => 0.8,
                'Plank' => 0.15,
                'Run' => 10.0,
                'Sprint' => 20.0,
                'Swim' => 15.0,
            ],
            default => [],
        };
    }
}
