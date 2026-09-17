<?php

namespace App\Services\Reports;

use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Enums\IncidentState;
use App\Enums\IncidentType;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentIncident;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ReportQueryService
{
    /** @param array<string, mixed> $filters */
    public function equipment(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = isset($filters['status']) ? EquipmentStatus::from($filters['status']) : null;
        $categoryId = (int) ($filters['category'] ?? 0);
        $query = Equipment::query()
            ->with('category:id,name')
            ->when($search !== '', fn (Builder $query): Builder => $query->where(
                fn (Builder $query): Builder => $query
                    ->where('equipment_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('asset_number', 'like', "%{$search}%"),
            ))
            ->when($status, fn (Builder $query): Builder => $query->where('status', $status->value))
            ->when($categoryId > 0, fn (Builder $query): Builder => $query->where('category_id', $categoryId));

        return [
            'equipment' => $query->orderBy('equipment_code')->paginate(20)->withQueryString(),
            'categories' => EquipmentCategory::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => EquipmentStatus::cases(),
            'statusCounts' => Equipment::query()
                ->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
        ];
    }

    /** @param array<string, mixed> $filters */
    public function borrowing(array $filters): array
    {
        $status = isset($filters['status']) ? BorrowRequestStatus::from($filters['status']) : null;
        $from = isset($filters['from']) ? Carbon::parse($filters['from']) : null;
        $to = isset($filters['to']) ? Carbon::parse($filters['to']) : null;
        $applyFilters = function (Builder $query) use ($status, $from, $to): Builder {
            return $query
                ->when($status, fn (Builder $query): Builder => $query->where('status', $status->value))
                ->when($from, fn (Builder $query): Builder => $query->whereDate('borrow_date', '>=', $from))
                ->when($to, fn (Builder $query): Builder => $query->whereDate('borrow_date', '<=', $to));
        };

        return [
            'borrowRequests' => $applyFilters(
                BorrowRequest::query()->with('borrower:id,name')->withCount('items'),
            )->latest('borrow_date')->paginate(20)->withQueryString(),
            'statuses' => BorrowRequestStatus::cases(),
            'statusCounts' => $applyFilters(BorrowRequest::query())
                ->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
        ];
    }

    /** @param array<string, mixed> $filters */
    public function overdue(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $activeStatuses = [
            BorrowRequestStatus::Approved->value,
            BorrowRequestStatus::ReadyForPickup->value,
            BorrowRequestStatus::Borrowed->value,
            BorrowRequestStatus::Overdue->value,
        ];
        $query = BorrowRequest::query()
            ->with('borrower:id,name,email')
            ->withCount('items')
            ->whereDate('expected_return_date', '<', today())
            ->whereIn('status', $activeStatuses)
            ->when($search !== '', fn (Builder $query): Builder => $query->where(
                fn (Builder $query): Builder => $query
                    ->where('request_no', 'like', "%{$search}%")
                    ->orWhereHas('borrower', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%")),
            ));

        return [
            'totalOverdue' => (clone $query)->count(),
            'borrowRequests' => $query->oldest('expected_return_date')->paginate(20)->withQueryString(),
        ];
    }

    /** @param array<string, mixed> $filters */
    public function damage(array $filters): array
    {
        $type = isset($filters['type']) ? IncidentType::from($filters['type']) : null;
        $state = isset($filters['resolution']) ? IncidentState::from($filters['resolution']) : null;
        $query = EquipmentIncident::query()
            ->with([
                'equipment:id,equipment_code,name,status',
                'borrowRequest:id,request_no',
                'reporter:id,name',
            ])
            ->when($type, fn (Builder $query): Builder => $query->where('type', $type->value))
            ->when($state === IncidentState::Open, fn (Builder $query): Builder => $query->whereNull('resolved_at'))
            ->when($state === IncidentState::Resolved, fn (Builder $query): Builder => $query->whereNotNull('resolved_at'));

        return [
            'incidents' => $query->latest('reported_at')->paginate(20)->withQueryString(),
            'types' => IncidentType::cases(),
            'states' => IncidentState::cases(),
            'openCount' => EquipmentIncident::query()->whereNull('resolved_at')->count(),
            'resolvedCount' => EquipmentIncident::query()->whereNotNull('resolved_at')->count(),
        ];
    }
}
