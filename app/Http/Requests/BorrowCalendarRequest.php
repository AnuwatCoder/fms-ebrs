<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BorrowCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('equipment.view') === true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('month')) {
            $this->merge(['month' => today()->format('Y-m')]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('equipment_categories', 'id')
                    ->where('active', true)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
