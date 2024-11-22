<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseLog extends Model
{
    use HasFactory;

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
     * Get the exercise that owns the exercise log.
     */
    public function exercise(): BelongsTo
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
