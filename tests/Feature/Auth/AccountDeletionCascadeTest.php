<?php

namespace Tests\Feature\Auth;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionCascadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_deletion_soft_deletes_user_and_cascades_to_workouts_and_logs(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create();
        $workout = Workout::factory()->forUser($user)->forGroup($group)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);
        $log = WorkoutExerciseLog::factory()->create([
            'workout_exercise_id' => $workoutExercise->id,
            'repetitions' => 10,
            'exercise_metric' => null,
            'metric_unit_id' => null,
        ]);

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertSoftDeleted($user);
        $this->assertSoftDeleted($workout);
        $this->assertSoftDeleted($workoutExercise);
        $this->assertSoftDeleted($log);
    }
}
