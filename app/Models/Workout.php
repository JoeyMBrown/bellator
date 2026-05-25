<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workout extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workout_date',
        'workout_type_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'workout_date' => 'datetime',
    ];

    public function workoutType(): BelongsTo
    {
        return $this->belongsTo(WorkoutType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'workout_exercises')
            ->withTimestamps()
            ->using(WorkoutExercise::class)
            ->withPivot('id', 'exercise_id', 'workout_id');
    }

    public function workoutExercises(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkoutExercise::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'workout_groups')
            ->withTimestamps();
    }
}
