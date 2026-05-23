<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupService
{
    public function generateInviteCode(): string
    {
        do {
            $code = 'BELL-'.strtoupper(Str::random(8));
        } while (Group::where('invite_code', $code)->exists());

        return $code;
    }

    public function create(User $owner, array $attributes): Group
    {
        return DB::transaction(function () use ($owner, $attributes) {
            $group = Group::create([
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'timezone' => $attributes['timezone'] ?? $owner->timezone ?? 'UTC',
                'invite_code' => $this->generateInviteCode(),
                'created_by_user_id' => $owner->id,
            ]);

            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $owner->id,
                'role' => GroupMember::ROLE_OWNER,
                'joined_at' => now(),
            ]);

            return $group;
        });
    }

    public function joinByCode(User $user, string $code): ?Group
    {
        $group = Group::where('invite_code', $code)->first();

        if ($group === null) {
            return null;
        }

        if ($group->hasMember($user)) {
            return $group;
        }

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => GroupMember::ROLE_MEMBER,
            'joined_at' => now(),
        ]);

        return $group;
    }

    public function regenerateInviteCode(Group $group): Group
    {
        $group->update(['invite_code' => $this->generateInviteCode()]);

        return $group;
    }

    public function removeMember(Group $group, User $user): void
    {
        $group->members()
            ->where('user_id', $user->id)
            ->where('role', '!=', GroupMember::ROLE_OWNER)
            ->delete();
    }

    public function changeRole(Group $group, User $user, string $role): void
    {
        $group->members()
            ->where('user_id', $user->id)
            ->where('role', '!=', GroupMember::ROLE_OWNER)
            ->update(['role' => $role]);
    }
}
