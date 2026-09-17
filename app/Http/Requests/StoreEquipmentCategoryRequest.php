<?php

namespace App\Http\Requests;

use App\Models\EquipmentCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EquipmentCategory::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }
}
