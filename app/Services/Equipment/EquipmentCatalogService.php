<?php

namespace App\Services\Equipment;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EquipmentCatalogService
{
    /** @param array<string, mixed> $filters */
    public function index(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = isset($filters['status']) ? EquipmentStatus::from($filters['status']) : null;
        $categoryId = (int) ($filters['category'] ?? 0);
        $equipment = Equipment::query()
            ->with('category:id,name')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('equipment_code', 'like', "%{$search}%")
                        ->orWhere('asset_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($status, fn (Builder $query): Builder => $query->where('status', $status->value))
            ->when($categoryId > 0, fn (Builder $query): Builder => $query->where('category_id', $categoryId))
            ->orderBy('equipment_code')
            ->paginate(15)
            ->withQueryString();

        return [
            'equipment' => $equipment,
            'categories' => $this->activeCategories(),
            'statuses' => EquipmentStatus::cases(),
        ];
    }

    /** @return array<string, mixed> */
    public function createForm(): array
    {
        return [
            'categories' => $this->activeCategories(),
            'statuses' => EquipmentStatus::cases(),
        ];
    }

    /** @return Collection<int, EquipmentCategory> */
    private function activeCategories(): Collection
    {
        return EquipmentCategory::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
