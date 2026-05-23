<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\GroupExercisePoints;
use App\Models\GroupMember;
use App\Models\User;

class GroupExercisePointsPolicy
{
    public function viewAny(User $user, Group $group): bool
    {
        return $group->hasMember($user);
    }

    public function view(User $user, GroupExercisePoints $points): bool
    {
        return $points->group !== null && $points->group->hasMember($user);
    }

    public function create(User $user, Group $group): bool
    {
        return $this->canManage($user, $group);
    }

    public function update(User $user, GroupExercisePoints $points): bool
    {
        return $points->group !== null && $this->canManage($user, $points->group);
    }

    public function delete(User $user, GroupExercisePoints $points): bool
    {
        return $points->group !== null && $this->canManage($user, $points->group);
    }

    protected function canManage(User $user, Group $group): bool
    {
        $role = $group->roleFor($user);

        return in_array($role, [GroupMember::ROLE_OWNER, GroupMember::ROLE_ADMIN], true);
    }
}
