<?php

namespace App\Http\Requests\Reports;

use App\Enums\EquipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('report.equipment') === true;
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
