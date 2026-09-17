<?php

namespace App\Actions\Equipment;

use App\Models\EquipmentCategory;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteEquipmentCategory
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, EquipmentCategory $category): void
    {
        DB::transaction(function () use ($actor, $category): void {
            $lockedCategory = EquipmentCategory::query()
                ->whereKey($category->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedCategory->equipment()->withTrashed()->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'ไม่สามารถลบหมวดหมู่ที่มีอุปกรณ์อ้างอิงอยู่ได้ กรุณาปิดใช้งานแทน',
                ]);
            }

            $oldValues = $lockedCategory->only(['name', 'code', 'description', 'active']);
            $this->auditLogger->record($actor, 'category.deleted', $lockedCategory, $oldValues);
            $lockedCategory->delete();
        });
    }
}
