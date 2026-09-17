<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('role')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z][A-Za-z0-9 _-]*$/',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($this->route('role')),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'integer',
                'distinct',
                Rule::exists('permissions', 'id')->where('guard_name', 'web'),
            ],
        ];
    }
}
