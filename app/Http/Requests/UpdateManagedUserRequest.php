<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => [
                'integer',
                'distinct',
                Rule::exists('roles', 'id')->where('guard_name', 'web'),
            ],
        ];
    }
}
