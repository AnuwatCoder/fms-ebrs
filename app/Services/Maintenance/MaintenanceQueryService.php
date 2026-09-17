<?php

namespace App\Services\Maintenance;

use App\Enums\EquipmentStatus;
use App\Enums\IncidentState;
use App\Enums\IncidentType;
use App\Models\Equipment;
use App\Models\EquipmentIncident;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceQueryService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function index(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $type = isset($filters['type']) ? IncidentType::from($filters['type']) : null;
        $state = isset($filters['state']) ? IncidentState::from($filters['state']) : null;

        return [
            'incidents' => EquipmentIncident::query()
                ->with([
                    'equipment:id,public_id,category_id,equipment_code,name,status',
                    'equipment.category:id,name',
                    'reporter:id,name',
                    'resolver:id,name',
                ])
                ->when($search !== '', fn (Builder $query): Builder => $query->where(
                    fn (Builder $query): Builder => $query
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('resolution', 'like', "%{$search}%")
                        ->orWhereHas('equipment', fn (Builder $query): Builder => $query
                            ->where('equipment_code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")),
                ))
                ->when($type, fn (Builder $query): Builder => $query->where('type', $type->value))
                ->when(
                    $state === IncidentState::Open,
                    fn (Builder $query): Builder => $query->whereNull('resolved_at'),
                )
                ->when(
                    $state === IncidentState::Resolved,
                    fn (Builder $query): Builder => $query->whereNotNull('resolved_at'),
                )
                ->latest('reported_at')
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'types' => IncidentType::cases(),
            'states' => IncidentState::cases(),
            'openCount' => EquipmentIncident::query()->whereNull('resolved_at')->count(),
            'resolvedCount' => EquipmentIncident::query()->whereNotNull('resolved_at')->count(),
        ];
    }

    /** @return array<string, mixed> */
    public function createForm(): array
    {
        return [
            'equipment' => Equipment::query()
                ->with('category:id,name')
                ->where('active', true)
                ->whereDoesntHave('incidents', fn (Builder $query): Builder => $query->whereNull('resolved_at'))
                ->orderBy('equipment_code')
                ->get(['id', 'public_id', 'category_id', 'equipment_code', 'name', 'status']),
            'types' => IncidentType::cases(),
        ];
    }

    /** @return array<string, mixed> */
    public function resolveForm(EquipmentIncident $incident): array
    {
        return [
            'incident' => $incident->load([
                'equipment:id,public_id,category_id,equipment_code,name,status',
                'equipment.category:id,name',
                'reporter:id,name',
            ]),
            'equipmentStatuses' => EquipmentStatus::incidentResolutionOptions(),
        ];
    }
}
