<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;

class ExercisePolicy
{
    public function viewAny(User $user, Group $group): bool
    {
        return $group->hasMember($user);
    }

    public function view(User $user, Exercise $exercise): bool
    {
        return $exercise->group !== null && $exercise->group->hasMember($user);
    }

    public function create(User $user, Group $group): bool
    {
        return $group->hasMember($user);
    }

    public function update(User $user, Exercise $exercise): bool
    {
        $role = $exercise->group?->roleFor($user);

        if (in_array($role, [GroupMember::ROLE_OWNER, GroupMember::ROLE_ADMIN], true)) {
            return true;
        }

        return $role === GroupMember::ROLE_MEMBER
            && $exercise->created_by_user_id === $user->id;
    }

    public function delete(User $user, Exercise $exercise): bool
    {
        return $this->update($user, $exercise);
    }
}
