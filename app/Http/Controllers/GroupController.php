<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Models\Workout;
use App\Services\ActivityFeedService;
use App\Services\GroupService;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function __construct(
        protected GroupService $groups,
        protected LeaderboardService $leaderboard,
        protected ActivityFeedService $feed,
    ) {}

    public function create(): Response
    {
        return Inertia::render('Groups/Create');
    }

    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $this->authorize('create', Group::class);

        $group = $this->groups->create(Auth::user(), $request->validated());

        return redirect()
            ->route('groups.show', $group)
            ->with('success', 'Group created.');
    }

    public function show(Request $request, Group $group): Response
    {
        $this->authorize('view', $group);

        $group->load(['members.user']);

        $window = $this->resolveWindow($request->query('window'));
        $page = max(1, (int) $request->query('feed_page', 1));

        $feed = $this->feed->forGroup($group, $page);

        return Inertia::render('Groups/Show', [
            'group' => $this->presentGroup($group),
            'leaderboard' => [
                'window' => $window,
                'rows' => $this->leaderboard->forGroup($group, $window)->all(),
            ],
            'feed' => [
                'data' => $feed->getCollection()->map(fn (Workout $w) => $this->presentWorkoutForFeed($w))->all(),
                'meta' => $this->presentFeedMeta($feed),
            ],
        ]);
    }

    public function edit(Group $group): Response
    {
        $this->authorize('update', $group);

        $group->load(['members.user']);

        return Inertia::render('Groups/Edit', [
            'group' => $this->presentGroup($group),
        ]);
    }

    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $this->authorize('update', $group);

        $group->update($request->validated());

        return redirect()
            ->route('groups.edit', $group)
            ->with('success', 'Group updated.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $this->authorize('delete', $group);

        $group->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Group deleted.');
    }

    public function regenerateInviteCode(Group $group): RedirectResponse
    {
        $this->authorize('regenerateInviteCode', $group);

        $this->groups->regenerateInviteCode($group);

        return redirect()
            ->route('groups.edit', $group)
            ->with('success', 'Invite code regenerated.');
    }

    protected function presentGroup(Group $group): array
    {
        $user = Auth::user();

        return [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'invite_code' => $group->invite_code,
            'timezone' => $group->timezone,
            'created_at' => $group->created_at,
            'role' => $group->roleFor($user),
            'members' => $group->members->map(fn ($member) => [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'name' => $member->user?->name ?? 'Former member',
                'email' => $member->user?->email,
                'role' => $member->role,
                'joined_at' => $member->joined_at,
            ])->values()->all(),
        ];
    }

    protected function presentWorkoutForFeed(Workout $workout): array
    {
        return [
            'id' => $workout->id,
            'workout_date' => $workout->workout_date,
            'notes' => $workout->notes,
            'group_points_earned' => $workout->group_points_earned !== null
                ? (float) $workout->group_points_earned
                : 0.0,
            'user' => [
                'id' => $workout->user?->id,
                'name' => $workout->user?->name ?? 'Former member',
            ],
            'exercises' => $workout->workoutExercises->map(fn ($we) => [
                'id' => $we->exercise?->id,
                'name' => $we->exercise?->name ?? 'Removed exercise',
                'measurement_type' => $we->exercise?->measurement_type,
                'logs' => $we->workoutExerciseLogs->map(fn ($log) => [
                    'repetitions' => $log->repetitions !== null ? (int) $log->repetitions : null,
                    'exercise_metric' => $log->exercise_metric !== null ? (float) $log->exercise_metric : null,
                    'metric_unit_name' => $log->metricUnit?->name,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    protected function presentFeedMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }

    protected function resolveWindow(?string $window): string
    {
        return in_array($window, LeaderboardService::WINDOWS, true)
            ? $window
            : LeaderboardService::WINDOW_WEEK;
    }
}
