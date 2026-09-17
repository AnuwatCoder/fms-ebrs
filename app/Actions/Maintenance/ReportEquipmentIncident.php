<?php

namespace App\Actions\Maintenance;

use App\Enums\IncidentType;
use App\Models\Equipment;
use App\Models\EquipmentIncident;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportEquipmentIncident
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(
        User $reporter,
        int $equipmentId,
        IncidentType $type,
        string $description,
    ): EquipmentIncident {
        return DB::transaction(function () use ($reporter, $equipmentId, $type, $description): EquipmentIncident {
            $equipment = Equipment::query()
                ->whereKey($equipmentId)
                ->lockForUpdate()
                ->firstOrFail();
            $hasOpenIncident = EquipmentIncident::query()
                ->where('equipment_id', $equipment->id)
                ->whereNull('resolved_at')
                ->lockForUpdate()
                ->exists();

            if ($hasOpenIncident) {
                throw ValidationException::withMessages([
                    'equipment_id' => 'อุปกรณ์นี้มีเหตุขัดข้องที่ยังไม่ได้ปิดอยู่แล้ว',
                ]);
            }

            $previousStatus = $equipment->status;
            $incident = EquipmentIncident::query()->create([
                'equipment_id' => $equipment->id,
                'type' => $type,
                'description' => trim($description),
                'reported_by' => $reporter->id,
                'reported_at' => now(),
            ]);
            $equipment->update(['status' => $type->equipmentStatus()]);

            $this->auditLogger->record($reporter, 'maintenance.reported', $incident, [], [
                'equipment_id' => $equipment->id,
                'type' => $type->value,
                'equipment_status_before' => $previousStatus->value,
                'equipment_status_after' => $type->equipmentStatus()->value,
            ]);

            return $incident->load('equipment');
        });
    }
}
