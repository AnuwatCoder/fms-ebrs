<?php

namespace App\Actions\Equipment;

use App\Models\EquipmentCategory;
use App\Models\User;
use App\Services\Equipment\EquipmentCodeGenerator;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreateEquipmentCategory
{
    public function __construct(
        private AuditLogger $auditLogger,
        private EquipmentCodeGenerator $codeGenerator,
    ) {}

    /** @param array{name: string, description?: string|null, active: bool} $attributes */
    public function execute(User $actor, array $attributes): EquipmentCategory
    {
        return DB::transaction(function () use ($actor, $attributes): EquipmentCategory {
            $category = EquipmentCategory::query()->create([
                ...$attributes,
                'code' => $this->codeGenerator->nextCategoryCode(),
            ]);

            $this->auditLogger->record($actor, 'category.created', $category, [], [
                'name' => $category->name,
                'code' => $category->code,
                'active' => $category->active,
            ]);

            return $category;
        });
    }
}
