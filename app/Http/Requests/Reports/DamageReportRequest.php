<?php

namespace App\Http\Requests\Reports;

use App\Enums\IncidentState;
use App\Enums\IncidentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DamageReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('report.damage') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::enum(IncidentType::class)],
            'resolution' => ['nullable', Rule::enum(IncidentState::class)],
        ];
    }
}
