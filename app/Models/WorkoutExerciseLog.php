<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutExerciseLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'exercise_metric',
        'metric_unit_id',
        'repetitions',
        'workout_exercise_id',
    ];

    public function workoutExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutExercise::class);
    }

    public function metricUnit(): BelongsTo
    {
        return $this->belongsTo(MetricUnit::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(WorkoutExerciseLogPoints::class);
    }
}
