<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutExercise extends Model
{
    use HasFactory;

    /**
     * Get the workout exercise logs for the workout exercise.
     */
    public function workoutExerciseLogs(): HasMany
    {
        return $this->hasMany(WorkoutExerciseLog::class);
    }
}
