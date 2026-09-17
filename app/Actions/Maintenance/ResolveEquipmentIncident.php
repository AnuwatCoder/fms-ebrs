<?php

namespace App\Actions\Maintenance;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\EquipmentIncident;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResolveEquipmentIncident
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(
        User $resolver,
        EquipmentIncident $incident,
        string $resolution,
        EquipmentStatus $equipmentStatus,
    ): EquipmentIncident {
        return DB::transaction(function () use ($resolver, $incident, $resolution, $equipmentStatus): EquipmentIncident {
            $lockedIncident = EquipmentIncident::query()
                ->whereKey($incident->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedIncident->resolved_at !== null) {
                throw ValidationException::withMessages([
                    'resolution' => 'เหตุขัดข้องนี้ถูกปิดไปแล้ว',
                ]);
            }

            if (! in_array($equipmentStatus, EquipmentStatus::incidentResolutionOptions(), true)) {
                throw ValidationException::withMessages([
                    'equipment_status' => 'สถานะอุปกรณ์หลังปิดเหตุไม่ถูกต้อง',
                ]);
            }

            $equipment = Equipment::query()
                ->whereKey($lockedIncident->equipment_id)
                ->lockForUpdate()
                ->firstOrFail();
            $hasAnotherOpenIncident = EquipmentIncident::query()
                ->where('equipment_id', $equipment->id)
                ->whereKeyNot($lockedIncident->getKey())
                ->whereNull('resolved_at')
                ->lockForUpdate()
                ->exists();

            if ($equipmentStatus === EquipmentStatus::Available && $hasAnotherOpenIncident) {
                throw ValidationException::withMessages([
                    'equipment_status' => 'ยังไม่สามารถกำหนดให้พร้อมยืมได้ เนื่องจากมีเหตุขัดข้องอื่นที่ยังไม่ปิด',
                ]);
            }

            $previousEquipmentStatus = $equipment->status;
            $lockedIncident->update([
                'resolution' => trim($resolution),
                'resolved_at' => now(),
                'resolved_by' => $resolver->id,
            ]);
            $equipment->update(['status' => $equipmentStatus]);

            $this->auditLogger->record($resolver, 'maintenance.resolved', $lockedIncident, [], [
                'resolution' => $lockedIncident->resolution,
                'equipment_status_before' => $previousEquipmentStatus->value,
                'equipment_status_after' => $equipmentStatus->value,
            ]);

            return $lockedIncident->refresh()->load(['equipment', 'resolver']);
        });
    }
}
