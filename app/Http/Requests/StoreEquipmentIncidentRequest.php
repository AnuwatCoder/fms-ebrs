<?php

namespace App\Http\Requests;

use App\Enums\IncidentType;
use App\Models\EquipmentIncident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EquipmentIncident::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'equipment_id' => [
                'required',
                'integer',
                Rule::exists('equipment', 'id')->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::enum(IncidentType::class)],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }
}
