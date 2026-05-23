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

    public const MEASUREMENT_REPS_ONLY = 'reps_only';

    public const MEASUREMENT_WEIGHTED_REPS = 'weighted_reps';

    public const MEASUREMENT_DISTANCE = 'distance';

    public const MEASUREMENT_DURATION = 'duration';

    public const MEASUREMENT_TYPES = [
        self::MEASUREMENT_REPS_ONLY,
        self::MEASUREMENT_WEIGHTED_REPS,
        self::MEASUREMENT_DISTANCE,
        self::MEASUREMENT_DURATION,
    ];

    protected $fillable = [
        'name',
        'description',
        'group_id',
        'measurement_type',
        'created_by_user_id',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function workoutExerciseLogs(): HasMany
    {
        return $this->hasMany(WorkoutExerciseLog::class);
    }

    public function workout(): BelongsToMany
    {
        return $this->belongsToMany(Workout::class, 'workout_exercises')
            ->withTimestamps()
            ->using(WorkoutExercise::class)
            ->withPivot('id', 'exercise_id', 'workout_id');
    }

    public function points(): HasMany
    {
        return $this->hasMany(GroupExercisePoints::class);
    }

    public function activePointsForGroup(int $groupId): ?GroupExercisePoints
    {
        return $this->points()
            ->where('group_id', $groupId)
            ->whereNull('end_date')
            ->first();
    }
}
