<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory;

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
     * Get the workout type that owns the workout.
     */
    public function workoutType(): BelongsTo
    {
        return $this->belongsTo(WorkoutType::class);
    }

    /**
     * Get the user that owns the workout.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exercises for the workout.
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }
}
