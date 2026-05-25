<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinGroupRequest;
use App\Http\Requests\UpdateGroupMemberRoleRequest;
use App\Models\Group;
use App\Models\User;
use App\Services\GroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class GroupMemberController extends Controller
{
    public function __construct(protected GroupService $groups) {}

    public function join(JoinGroupRequest $request): RedirectResponse
    {
        $group = $this->groups->joinByCode(Auth::user(), $request->validated()['invite_code']);

        if ($group === null) {
            throw ValidationException::withMessages([
                'invite_code' => 'Invite code is not valid.',
            ]);
        }

        return redirect()
            ->route('groups.show', $group)
            ->with('success', 'Joined group.');
    }

    public function leave(Group $group): RedirectResponse
    {
        $this->authorize('leave', $group);

        $group->members()
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'You left the group.');
    }

    public function destroy(Group $group, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $group);

        if ($user->id === Auth::id()) {
            throw ValidationException::withMessages([
                'user_id' => 'Use the leave action to remove yourself.',
            ]);
        }

        $this->groups->removeMember($group, $user);

        return redirect()
            ->route('groups.edit', $group)
            ->with('success', 'Member removed.');
    }

    public function updateRole(UpdateGroupMemberRoleRequest $request, Group $group, User $user): RedirectResponse
    {
        $this->authorize('promoteAdmin', $group);

        $this->groups->changeRole($group, $user, $request->validated()['role']);

        return redirect()
            ->route('groups.edit', $group)
            ->with('success', 'Member role updated.');
    }
}
