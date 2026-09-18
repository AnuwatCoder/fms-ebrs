<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StopRoleSimulationRequest extends FormRequest
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
        return [];
    }
}
