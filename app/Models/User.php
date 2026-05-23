<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function workouts(): HasMany
    {
        return $this->hasMany(Workout::class);
    }

    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * On (soft) delete, cascade to the user's workouts and their nested
     * workout_exercises and workout_exercise_logs. FR-AUTH-6.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->isForceDeleting()) {
                return;
            }

            $user->workouts()->each(function (Workout $workout) {
                $workout->workoutExercises()->each(function (WorkoutExercise $we) {
                    $we->workoutExerciseLogs()->delete();
                    $we->delete();
                });
                $workout->delete();
            });
        });
    }
}
