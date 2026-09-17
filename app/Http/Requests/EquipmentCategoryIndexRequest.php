<?php

namespace App\Http\Requests;

use App\Models\EquipmentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentCategoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', EquipmentCategory::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', Rule::in(['0', '1'])],
        ];
    }
}
