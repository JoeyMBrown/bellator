<?php

namespace App\Http\Requests;

use App\Models\GroupMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in([GroupMember::ROLE_ADMIN, GroupMember::ROLE_MEMBER])],
        ];
    }
}
