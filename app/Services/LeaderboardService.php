<?php

namespace App\Services;

use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public const WINDOW_WEEK = 'week';

    public const WINDOW_MONTH = 'month';

    public const WINDOW_ALL = 'all';

    public const WINDOWS = [self::WINDOW_WEEK, self::WINDOW_MONTH, self::WINDOW_ALL];

    /**
     * Return the leaderboard for the group in the requested time window.
     *
     * Each row: ['user_id', 'name', 'rank', 'total_points', 'workout_count', 'last_workout_date'].
     */
    public function forGroup(Group $group, string $window): Collection
    {
        $startUtc = $this->windowStartUtc($group, $window);

        // When performing aggregate queries it makes sense to reach for the raw query builder over Eloquent.
        $workoutStats = DB::table('workouts')
            ->join('workout_groups', 'workout_groups.workout_id', '=', 'workouts.id')
            ->where('workout_groups.group_id', $group->id)
            ->whereNull('workouts.deleted_at')
            ->when($startUtc, fn ($q) => $q->where('workouts.workout_date', '>=', $startUtc))
            ->select(
                'workouts.user_id',
                DB::raw('COUNT(DISTINCT workouts.id) as workout_count'),
                DB::raw('MAX(workouts.workout_date) as last_workout_date'),
            )
            ->groupBy('workouts.user_id')
            ->get()
            ->keyBy('user_id');

        // When performing aggregate queries it makes sense to reach for the raw query builder over Eloquent.
        $pointsStats = DB::table('workout_exercise_log_points')
            ->join('workout_exercise_logs', 'workout_exercise_logs.id', '=', 'workout_exercise_log_points.workout_exercise_log_id')
            ->join('workout_exercises', 'workout_exercises.id', '=', 'workout_exercise_logs.workout_exercise_id')
            ->join('workouts', 'workouts.id', '=', 'workout_exercises.workout_id')
            ->where('workout_exercise_log_points.group_id', $group->id)
            ->whereNull('workout_exercise_logs.deleted_at')
            ->whereNull('workout_exercises.deleted_at')
            ->whereNull('workouts.deleted_at')
            ->when($startUtc, fn ($q) => $q->where('workouts.workout_date', '>=', $startUtc))
            ->select('workouts.user_id', DB::raw('SUM(workout_exercise_log_points.points_earned) as total_points'))
            ->groupBy('workouts.user_id')
            ->get()
            ->keyBy('user_id');

        $members = $group->users()
            ->select('users.id', 'users.name')
            ->get();

        return $members
            ->map(function ($user) use ($workoutStats, $pointsStats) {
                $w = $workoutStats->get($user->id);
                $p = $pointsStats->get($user->id);

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'total_points' => $p !== null ? (float) $p->total_points : 0.0,
                    'workout_count' => $w !== null ? (int) $w->workout_count : 0,
                    'last_workout_date' => $w !== null ? $w->last_workout_date : null,
                ];
            })
            ->sortByDesc(fn ($row) => $row['total_points'])
            ->values()
            ->map(function ($row, $index) {
                $row['rank'] = $index + 1;

                return $row;
            });
    }

    /**
     * Per-user stats restricted to one member (used by the in-group profile page).
     */
    public function statsForUser(Group $group, string $userId, string $window): array
    {
        $rows = $this->forGroup($group, $window);
        $match = $rows->firstWhere('user_id', $userId);

        return $match ?? [
            'user_id' => $userId,
            'name' => null,
            'total_points' => 0.0,
            'workout_count' => 0,
            'last_workout_date' => null,
            'rank' => null,
        ];
    }

    /**
     * Return the inclusive UTC instant at which the window starts, or null for "all time".
     *
     * Week starts Monday 00:00 in the group's timezone.
     * Month starts on the 1st 00:00 in the group's timezone.
     */
    public function windowStartUtc(Group $group, string $window): ?string
    {
        if ($window === self::WINDOW_ALL) {
            return null;
        }

        $tz = $group->timezone ?: 'UTC';
        $now = Carbon::now($tz);

        $start = match ($window) {
            self::WINDOW_WEEK => $now->copy()->startOfWeek(Carbon::MONDAY),
            self::WINDOW_MONTH => $now->copy()->startOfMonth(),
            default => null,
        };

        return $start?->setTimezone('UTC')->format('Y-m-d H:i:s');
    }
}
