<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentAvailabilityResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equipment_code' => $this->equipment_code,
            'name' => $this->name,
            'brand' => $this->brand,
            'model' => $this->model,
            'location' => $this->location,
            'category' => [
                'name' => $this->category->name,
            ],
        ];
    }
}
