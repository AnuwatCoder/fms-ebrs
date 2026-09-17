<?php

namespace App\Actions\Equipment;

use App\Models\Equipment;
use App\Models\User;
use App\Services\Equipment\EquipmentCodeGenerator;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreateEquipment
{
    public function __construct(
        private AuditLogger $auditLogger,
        private EquipmentCodeGenerator $codeGenerator,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $actor, array $attributes): Equipment
    {
        return DB::transaction(function () use ($actor, $attributes): Equipment {
            $equipment = Equipment::query()->create([
                ...$attributes,
                'equipment_code' => $this->codeGenerator->nextEquipmentCode(),
            ]);

            $this->auditLogger->record($actor, 'equipment.created', $equipment, [], [
                'equipment_code' => $equipment->equipment_code,
                'name' => $equipment->name,
                'status' => $equipment->status->value,
            ]);

            return $equipment;
        });
    }
}
