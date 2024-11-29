<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workout extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'workout_date',
        'workout_type_id',
        'user_id'
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
     * The exercises that belong to the workout.
     */
    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'workout_exercises')->withTimestamps()->using(WorkoutExercise::class);
    }

    /**
     * Return a formatted workout date.
     */
    protected function workoutDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (new Carbon($value, 'America/Detroit'))->format('F j, Y'), // TODO: Add support of users current timezone.
        );
    }
}
