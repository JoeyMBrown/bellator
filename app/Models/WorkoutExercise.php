<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutExercise extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'workout_id',
        'exercise_id'
    ];

    /**
     * Get the workout exercise logs for the workout exercise.
     */
    public function workoutExerciseLogs(): HasMany
    {
        return $this->hasMany(WorkoutExerciseLog::class);
    }
}
