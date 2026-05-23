<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Services\GroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function __construct(protected GroupService $groups) {}

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

    public function show(Group $group): Response
    {
        $this->authorize('view', $group);

        $group->load(['members.user']);

        return Inertia::render('Groups/Show', [
            'group' => $this->presentGroup($group),
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
}
