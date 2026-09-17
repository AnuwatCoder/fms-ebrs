<?php

namespace App\Http\Requests;

use App\Enums\EquipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveEquipmentIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resolve', $this->route('incident')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'resolution' => ['required', 'string', 'max:5000'],
            'equipment_status' => [
                'required',
                Rule::in(array_map(
                    fn (EquipmentStatus $status): string => $status->value,
                    EquipmentStatus::incidentResolutionOptions(),
                )),
            ],
        ];
    }
}
