<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercise extends Model
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
     * Get the exercise type that owns the exercise.
     */
    public function exerciseType(): BelongsTo
    {
        return $this->belongsTo(ExerciseType::class);
    }

    /**
     * The workout exercise logs that belong to the exercise.
     */
    public function workoutExerciseLogs(): HasMany
    {
        return $this->hasMany(WorkoutExerciseLog::class);
    }

    /**
     * The workout that owns the exercise.
     */
    public function workout(): BelongsToMany
    {
        return $this->belongsToMany(Workout::class, 'workout_exercises')->withTimestamps()->using(WorkoutExercise::class);
    }
}
