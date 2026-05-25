<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkoutFeedResource;
use App\Models\Group;
use App\Models\User;
use App\Services\ActivityFeedService;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupMemberProfileController extends Controller
{
    public function __construct(
        protected LeaderboardService $leaderboard,
        protected ActivityFeedService $feed,
    ) {}

    public function show(Request $request, Group $group, User $user): Response
    {
        $this->authorize('view', $group);
        abort_if(! $group->hasMember($user), 404);

        $window = $this->resolveWindow($request->query('window'));
        $windowStats = $this->leaderboard->statsForUser($group, $user->id, $window);
        $aggregate = $this->feed->aggregateForUserInGroup($user, $group);

        $memberRow = $group->members()
            ->where('user_id', $user->id)
            ->first();

        $recentWorkouts = $this->feed->forUserInGroup($user, $group);

        return Inertia::render('Groups/Members/Show', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
            ],
            'profile' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $memberRow?->role,
                'member_since' => $memberRow?->joined_at,
            ],
            'window' => $window,
            'windowStats' => $windowStats,
            'aggregate' => $aggregate,
            'recentWorkouts' => WorkoutFeedResource::collection($recentWorkouts)->resolve($request),
        ]);
    }

    protected function resolveWindow(?string $window): string
    {
        return in_array($window, LeaderboardService::WINDOWS, true)
            ? $window
            : LeaderboardService::WINDOW_WEEK;
    }
}
