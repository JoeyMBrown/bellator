<?php

namespace Tests\Unit\Policies;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseLog;
use App\Policies\WorkoutExerciseLogPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutExerciseLogPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected WorkoutExerciseLogPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new WorkoutExerciseLogPolicy;
    }

    protected function makeLog(User $owner, Group $group): WorkoutExerciseLog
    {
        $exercise = Exercise::factory()->forGroup($group)->repsOnly()->create([
            'created_by_user_id' => $owner->id,
        ]);
        $workout = Workout::factory()->forUser($owner)->forGroup($group)->create();
        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);

        return WorkoutExerciseLog::factory()->create([
            'workout_exercise_id' => $workoutExercise->id,
        ]);
    }

    public function test_owner_can_view_their_log(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        $log = $this->makeLog($owner, $group);

        $this->assertTrue($this->policy->view($owner, $log));
    }

    public function test_group_member_can_view_log_in_tagged_group(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $viewer->id,
            'role' => GroupMember::ROLE_MEMBER,
        ]);

        $log = $this->makeLog($owner, $group);

        $this->assertTrue($this->policy->view($viewer, $log));
    }

    public function test_only_owner_can_update_log(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $group = Group::factory()->withOwner($owner)->create();
        GroupMember::factory()->create([
            'group_id' => $group->id,
            'user_id' => $other->id,
            'role' => GroupMember::ROLE_ADMIN,
        ]);
        $log = $this->makeLog($owner, $group);

        $this->assertTrue($this->policy->update($owner, $log));
        $this->assertFalse($this->policy->update($other, $log));
    }
}
