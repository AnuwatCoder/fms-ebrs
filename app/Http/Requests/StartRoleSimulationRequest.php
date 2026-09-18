<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartRoleSimulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->roles()
            ->where('name', 'SuperAdmin')
            ->where('guard_name', 'web')
            ->exists() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
                Rule::notIn(['SuperAdmin']),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'role.required' => 'กรุณาเลือกบทบาทที่ต้องการจำลอง',
            'role.exists' => 'ไม่พบบทบาทที่ต้องการจำลอง',
            'role.not_in' => 'ไม่สามารถจำลองบทบาท SuperAdmin ได้',
        ];
    }
}
