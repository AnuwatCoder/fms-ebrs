<?php

namespace App\Http\Requests;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Equipment::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'integer', 'exists:equipment_categories,id'],
            'status' => ['nullable', Rule::enum(EquipmentStatus::class)],
        ];
    }
}
