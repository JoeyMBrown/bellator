<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\GroupMember
 */
class GroupMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->whenLoaded(
                'user',
                fn () => $this->user?->name,
                'Former member'
            ),
            'email' => $this->whenLoaded(
                'user',
                fn () => $this->user?->email
            ),
            'role' => $this->role,
            'joined_at' => $this->joined_at,
        ];
    }
}
