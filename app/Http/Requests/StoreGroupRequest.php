<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:140'],
            'timezone' => ['nullable', 'string', 'max:64', 'timezone'],
            'preset_rubric' => ['nullable', 'string', 'in:strength,endurance,balanced'],
        ];
    }
}
