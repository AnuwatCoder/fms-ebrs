<?php

namespace App\Services\Borrowing;

use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequestItem;
use App\Models\Equipment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EquipmentAvailabilityService
{
    /** @return Collection<int, Equipment> */
    public function availableBetween(CarbonInterface $borrowDate, CarbonInterface $returnDate): Collection
    {
        return Equipment::query()
            ->with('category:id,name')
            ->where('active', true)
            ->whereIn('status', $this->schedulableEquipmentStatuses())
            ->whereDoesntHave('borrowItems', function (Builder $query) use ($borrowDate, $returnDate): void {
                $query->whereHas('borrowRequest', function (Builder $query) use ($borrowDate, $returnDate): void {
                    $this->applyOverlapConstraint(
                        $query,
                        $borrowDate,
                        $returnDate,
                        $this->requestBlockingStatuses(),
                    );
                });
            })
            ->orderBy('name')
            ->orderBy('equipment_code')
            ->get();
    }

    /**
     * @param  list<int>  $equipmentIds
     * @param  list<string>|null  $statuses
     */
    public function hasConflicts(
        array $equipmentIds,
        CarbonInterface $borrowDate,
        CarbonInterface $returnDate,
        ?array $statuses = null,
        ?int $excludeBorrowRequestId = null,
    ): bool {
        return BorrowRequestItem::query()
            ->whereIn('equipment_id', $equipmentIds)
            ->whereHas('borrowRequest', function (Builder $query) use (
                $borrowDate,
                $returnDate,
                $statuses,
                $excludeBorrowRequestId,
            ): void {
                $this->applyOverlapConstraint(
                    $query,
                    $borrowDate,
                    $returnDate,
                    $statuses ?? $this->requestBlockingStatuses(),
                );

                if ($excludeBorrowRequestId !== null) {
                    $query->whereKeyNot($excludeBorrowRequestId);
                }
            })
            ->exists();
    }

    /** @return list<string> */
    public function schedulableEquipmentStatuses(): array
    {
        return [
            EquipmentStatus::Available->value,
            EquipmentStatus::Reserved->value,
        ];
    }

    /** @return list<string> */
    public function approvedRequestBlockingStatuses(): array
    {
        return [
            BorrowRequestStatus::Approved->value,
            BorrowRequestStatus::ReadyForPickup->value,
            BorrowRequestStatus::Borrowed->value,
            BorrowRequestStatus::Overdue->value,
        ];
    }

    /** @return list<string> */
    private function requestBlockingStatuses(): array
    {
        return [
            BorrowRequestStatus::Pending->value,
            ...$this->approvedRequestBlockingStatuses(),
        ];
    }

    /** @param list<string> $statuses */
    private function applyOverlapConstraint(
        Builder $query,
        CarbonInterface $borrowDate,
        CarbonInterface $returnDate,
        array $statuses,
    ): void {
        $query
            ->whereIn('status', $statuses)
            ->whereDate('borrow_date', '<=', $returnDate->toDateString())
            ->whereDate('expected_return_date', '>=', $borrowDate->toDateString());
    }
}
