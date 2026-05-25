<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'invite_code' => ['required', 'string', 'max:16'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('invite_code'))) {
            $this->merge([
                'invite_code' => strtoupper(trim($this->input('invite_code'))),
            ]);
        }
    }
}
