<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExercisePointHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'points',
        'exercise_metric',
        'metric_unit_id',
        'start_date',
        'end_date'
    ];

    /**
     * Get the exercise that owns the execise point history.
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(ExercisePointHistory::class);
    }

    /**
     * Get the metric unit that owns the execise point history.
     */
    public function metricUnit(): BelongsTo
    {
        return $this->belongsTo(MetricUnit::class);
    }
}
