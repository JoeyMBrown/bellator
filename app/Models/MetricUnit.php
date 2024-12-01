<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetricUnit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * The workout exercise logs that belong to the metric unit.
     */
    public function workoutExerciseLogs(): HasMany
    {
        return $this->hasMany(WorkoutExerciseLog::class);
    }

    /**
     * The exercise points history that belong to the metric unit.
     */
    public function exercisePointsHistory(): HasMany
    {
        return $this->hasMany(ExercisePointHistory::class);
    }
}
