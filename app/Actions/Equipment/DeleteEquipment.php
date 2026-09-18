<?php

namespace App\Actions\Equipment;

use App\Models\Equipment;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteEquipment
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, Equipment $equipment): void
    {
        DB::transaction(function () use ($actor, $equipment): void {
            $lockedEquipment = Equipment::query()
                ->whereKey($equipment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEquipment->borrowItems()->exists() || $lockedEquipment->incidents()->exists()) {
                throw ValidationException::withMessages([
                    'equipment' => 'ไม่สามารถลบอุปกรณ์ที่มีประวัติการยืมหรือเหตุขัดข้องได้ กรุณาปิดใช้งานแทน',
                ]);
            }

            $oldValues = $lockedEquipment->only([
                'equipment_code',
                'asset_number',
                'name',
                'status',
                'active',
            ]);

            $this->auditLogger->record($actor, 'equipment.deleted', $lockedEquipment, $oldValues);
            $lockedEquipment->delete();
        });
    }
}
