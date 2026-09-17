<?php

namespace App\Services\Equipment;

use App\Models\EquipmentCategory;
use Illuminate\Database\Eloquent\Builder;

class EquipmentCategoryQueryService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function index(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $active = $filters['active'] ?? null;

        return [
            'categories' => EquipmentCategory::query()
                ->withCount('equipment')
                ->when($search !== '', fn (Builder $query): Builder => $query->where(
                    fn (Builder $query): Builder => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%"),
                ))
                ->when(
                    in_array($active, ['0', '1'], true),
                    fn (Builder $query): Builder => $query->where('active', $active === '1'),
                )
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
        ];
    }
}
