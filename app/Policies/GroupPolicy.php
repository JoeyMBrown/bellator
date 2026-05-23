<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;

class GroupPolicy
{
    public function view(User $user, Group $group): bool
    {
        return $group->hasMember($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Group $group): bool
    {
        return $this->canManage($user, $group);
    }

    public function delete(User $user, Group $group): bool
    {
        return $group->roleFor($user) === GroupMember::ROLE_OWNER;
    }

    public function regenerateInviteCode(User $user, Group $group): bool
    {
        return $this->canManage($user, $group);
    }

    public function manageMembers(User $user, Group $group): bool
    {
        return $this->canManage($user, $group);
    }

    public function promoteAdmin(User $user, Group $group): bool
    {
        return $group->roleFor($user) === GroupMember::ROLE_OWNER;
    }

    public function leave(User $user, Group $group): bool
    {
        $role = $group->roleFor($user);

        return $role !== null && $role !== GroupMember::ROLE_OWNER;
    }

    protected function canManage(User $user, Group $group): bool
    {
        $role = $group->roleFor($user);

        return in_array($role, [GroupMember::ROLE_OWNER, GroupMember::ROLE_ADMIN], true);
    }
}
