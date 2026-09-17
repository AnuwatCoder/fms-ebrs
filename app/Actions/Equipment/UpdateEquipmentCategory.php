<?php

namespace App\Actions\Equipment;

use App\Models\EquipmentCategory;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateEquipmentCategory
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param array{name: string, description?: string|null, active: bool} $attributes */
    public function execute(User $actor, EquipmentCategory $category, array $attributes): EquipmentCategory
    {
        return DB::transaction(function () use ($actor, $category, $attributes): EquipmentCategory {
            $lockedCategory = EquipmentCategory::query()
                ->whereKey($category->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $oldValues = $lockedCategory->only(['name', 'code', 'description', 'active']);

            $lockedCategory->update($attributes);

            $this->auditLogger->record(
                $actor,
                'category.updated',
                $lockedCategory,
                $oldValues,
                $lockedCategory->only(['name', 'code', 'description', 'active']),
            );

            return $lockedCategory->refresh();
        });
    }
}
