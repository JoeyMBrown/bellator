<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutExerciseLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'exercise_metric',
        'exercise_points'
    ];

    /**
     * Get the workout exercise that owns the exercise log.
     */
    public function workoutExercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * Get the metric unit that owns the exercise log.
     */
    public function metricUnit(): BelongsTo
    {
        return $this->belongsTo(MetricUnit::class);
    }
}
