<?php

namespace App\Actions\Equipment;

use App\Models\Equipment;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateEquipment
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $actor, Equipment $equipment, array $attributes): Equipment
    {
        return DB::transaction(function () use ($actor, $equipment, $attributes): Equipment {
            $lockedEquipment = Equipment::query()
                ->whereKey($equipment->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $fields = [
                'category_id',
                'asset_number',
                'name',
                'description',
                'brand',
                'model',
                'serial_number',
                'location',
                'purchase_date',
                'active',
            ];
            $oldValues = $lockedEquipment->only($fields);

            $lockedEquipment->update($attributes);

            $this->auditLogger->record(
                $actor,
                'equipment.updated',
                $lockedEquipment,
                $oldValues,
                $lockedEquipment->only($fields),
            );

            return $lockedEquipment->refresh();
        });
    }
}
