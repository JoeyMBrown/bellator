<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Group
 */
class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'invite_code' => $this->invite_code,
            'timezone' => $this->timezone,
            'created_at' => $this->created_at,
            'role' => $this->roleFor($request->user()),
            'members' => $this->whenLoaded(
                'members',
                fn () => GroupMemberResource::collection($this->members),
            ),
        ];
    }
}
