<?php

namespace App\Services\Borrowing;

use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class BorrowCalendarService
{
    public function __construct(private EquipmentAvailabilityService $availability) {}

    /**
     * @param  array{month: string, category_id?: int|string|null}  $filters
     * @return array<string, mixed>
     */
    public function calendar(array $filters): array
    {
        $month = CarbonImmutable::createFromFormat('!Y-m', $filters['month'])->locale('th');
        $start = $month->startOfMonth();
        $end = $month->endOfMonth();
        $categoryId = filled($filters['category_id'] ?? null) ? (int) $filters['category_id'] : null;

        $equipmentQuery = Equipment::query()
            ->where('active', true)
            ->when($categoryId, fn (Builder $query): Builder => $query->where('category_id', $categoryId));
        $totalEquipment = (clone $equipmentQuery)->count();
        $schedulableEquipmentIds = (clone $equipmentQuery)
            ->whereIn('status', $this->availability->schedulableEquipmentStatuses())
            ->pluck('id');

        $requests = BorrowRequest::query()
            ->whereIn('status', $this->availability->requestBlockingStatuses())
            ->whereDate('borrow_date', '<=', $end->toDateString())
            ->whereDate('expected_return_date', '>=', $start->toDateString())
            ->when(
                $schedulableEquipmentIds->isEmpty(),
                fn (Builder $query): Builder => $query->whereRaw('1 = 0'),
                fn (Builder $query): Builder => $query->whereHas(
                    'items',
                    fn (Builder $query): Builder => $query->whereIn('equipment_id', $schedulableEquipmentIds),
                ),
            )
            ->with([
                'items' => fn ($query) => $query
                    ->select(['id', 'borrow_request_id', 'equipment_id'])
                    ->whereIn('equipment_id', $schedulableEquipmentIds),
            ])
            ->get(['id', 'borrow_date', 'expected_return_date']);

        $days = collect(range(1, $month->daysInMonth))->map(function (int $day) use (
            $month,
            $requests,
            $schedulableEquipmentIds,
            $totalEquipment,
        ): array {
            $date = $month->setDay($day);
            $dayRequests = $requests->filter(
                fn (BorrowRequest $request): bool => $request->borrow_date->lte($date)
                    && $request->expected_return_date->gte($date),
            );
            $reservedIds = $dayRequests
                ->flatMap(fn (BorrowRequest $request) => $request->items->pluck('equipment_id'))
                ->unique();
            $reservedSchedulable = $reservedIds->intersect($schedulableEquipmentIds)->count();
            $available = max(0, $schedulableEquipmentIds->count() - $reservedSchedulable);

            return [
                'date' => $date,
                'available' => $available,
                'total' => $totalEquipment,
                'reserved' => $reservedIds->count(),
                'requests' => $dayRequests->count(),
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
            ];
        });

        $cells = collect(array_fill(0, $start->dayOfWeekIso - 1, null))
            ->concat($days);
        while ($cells->count() % 7 !== 0) {
            $cells->push(null);
        }

        return [
            'calendarWeeks' => $cells->chunk(7),
            'monthLabel' => $month->translatedFormat('F Y'),
            'currentMonth' => $month->format('Y-m'),
            'previousMonth' => $month->subMonth()->format('Y-m'),
            'nextMonth' => $month->addMonth()->format('Y-m'),
            'categories' => EquipmentCategory::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'selectedCategoryId' => $categoryId,
            'totalEquipment' => $totalEquipment,
            'schedulableEquipment' => $schedulableEquipmentIds->count(),
        ];
    }
}
