<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\WorkoutFeedCollection;
use App\Models\Group;
use App\Services\ActivityFeedService;
use App\Services\GroupService;
use App\Services\LeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'group' => new GroupResource($group),
            'leaderboard' => [
                'window' => $window,
                'rows' => $this->leaderboard->forGroup($group, $window)->all(),
            ],
            'feed' => (new WorkoutFeedCollection($feed))->resolve($request),
        ]);
    }

    public function edit(Request $request, Group $group): Response
    {
        $this->authorize('update', $group);

        $group->load(['members.user']);

        return Inertia::render('Groups/Edit', [
            'group' => new GroupResource($group),
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

    protected function resolveWindow(?string $window): string
    {
        return in_array($window, LeaderboardService::WINDOWS, true)
            ? $window
            : LeaderboardService::WINDOW_WEEK;
    }
}
