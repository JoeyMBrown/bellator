<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutExerciseLogPoints extends Model
{
    use HasFactory;

    protected $table = 'workout_exercise_log_points';

    protected $fillable = [
        'workout_exercise_log_id',
        'group_id',
        'points_earned',
    ];

    protected $casts = [
        'points_earned' => 'decimal:4',
    ];

    public function workoutExerciseLog(): BelongsTo
    {
        return $this->belongsTo(WorkoutExerciseLog::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
