<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ActivityFeedService
{
    public const PER_PAGE = 20;

    /**
     * Paginated reverse-chronological feed of workouts tagged to $group.
     */
    public function forGroup(Group $group, int $page = 1): LengthAwarePaginator
    {
        $pointsSum = DB::table('workout_exercise_log_points as welp')
            ->join('workout_exercise_logs as wel', 'wel.id', '=', 'welp.workout_exercise_log_id')
            ->join('workout_exercises as we', 'we.id', '=', 'wel.workout_exercise_id')
            ->where('welp.group_id', $group->id)
            ->whereNull('wel.deleted_at')
            ->whereNull('we.deleted_at')
            ->whereColumn('we.workout_id', 'workouts.id')
            ->selectRaw('COALESCE(SUM(welp.points_earned), 0)');

        return Workout::query()
            ->whereHas('groups', fn ($q) => $q->where('groups.id', $group->id))
            ->select('workouts.*')
            ->selectSub($pointsSum, 'group_points_earned')
            ->with([
                'user:id,name',
                'workoutExercises' => fn ($q) => $q->orderBy('created_at'),
                'workoutExercises.exercise:id,name,measurement_type,group_id',
                'workoutExercises.workoutExerciseLogs.metricUnit:id,name',
            ])
            ->orderByDesc('workout_date')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE, ['*'], 'feed_page', $page);
    }

    /**
     * Workouts a user has logged into a specific group (for the in-group profile).
     */
    public function forUserInGroup(User $user, Group $group, int $limit = 20): array
    {
        $pointsSum = DB::table('workout_exercise_log_points as welp')
            ->join('workout_exercise_logs as wel', 'wel.id', '=', 'welp.workout_exercise_log_id')
            ->join('workout_exercises as we', 'we.id', '=', 'wel.workout_exercise_id')
            ->where('welp.group_id', $group->id)
            ->whereNull('wel.deleted_at')
            ->whereNull('we.deleted_at')
            ->whereColumn('we.workout_id', 'workouts.id')
            ->selectRaw('COALESCE(SUM(welp.points_earned), 0)');

        return Workout::query()
            ->whereHas('groups', fn ($q) => $q->where('groups.id', $group->id))
            ->where('user_id', $user->id)
            ->select('workouts.*')
            ->selectSub($pointsSum, 'group_points_earned')
            ->with([
                'workoutExercises' => fn ($q) => $q->orderBy('created_at'),
                'workoutExercises.exercise:id,name,measurement_type,group_id',
                'workoutExercises.workoutExerciseLogs.metricUnit:id,name',
            ])
            ->orderByDesc('workout_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * Aggregate stats for a user in a group (all-time).
     */
    public function aggregateForUserInGroup(User $user, Group $group): array
    {
        $points = DB::table('workout_exercise_log_points as welp')
            ->join('workout_exercise_logs as wel', 'wel.id', '=', 'welp.workout_exercise_log_id')
            ->join('workout_exercises as we', 'we.id', '=', 'wel.workout_exercise_id')
            ->join('workouts as w', 'w.id', '=', 'we.workout_id')
            ->where('welp.group_id', $group->id)
            ->where('w.user_id', $user->id)
            ->whereNull('wel.deleted_at')
            ->whereNull('we.deleted_at')
            ->whereNull('w.deleted_at')
            ->sum('welp.points_earned');

        $workoutCount = DB::table('workouts')
            ->join('workout_groups', 'workout_groups.workout_id', '=', 'workouts.id')
            ->where('workout_groups.group_id', $group->id)
            ->where('workouts.user_id', $user->id)
            ->whereNull('workouts.deleted_at')
            ->count();

        return [
            'total_points' => (float) ($points ?? 0),
            'workout_count' => (int) $workoutCount,
        ];
    }
}
