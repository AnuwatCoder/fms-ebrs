<?php

namespace App\Http\Requests;

use App\Enums\IncidentState;
use App\Enums\IncidentType;
use App\Models\EquipmentIncident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', EquipmentIncident::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::enum(IncidentType::class)],
            'state' => ['nullable', Rule::enum(IncidentState::class)],
        ];
    }
}
