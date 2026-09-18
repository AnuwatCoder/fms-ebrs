<?php

namespace App\Actions\Borrowing;

use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequest;
use App\Models\BorrowRequestItem;
use App\Models\Equipment;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelBorrowRequest
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(User $borrower, BorrowRequest $borrowRequest): BorrowRequest
    {
        return DB::transaction(function () use ($borrower, $borrowRequest): BorrowRequest {
            $lockedRequest = BorrowRequest::query()
                ->whereKey($borrowRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->user_id !== $borrower->id) {
                throw new AuthorizationException;
            }

            if (! $lockedRequest->status->canTransitionTo(BorrowRequestStatus::Cancelled)) {
                throw ValidationException::withMessages([
                    'cancel' => 'ไม่สามารถยกเลิกคำขอในสถานะปัจจุบันได้',
                ]);
            }

            $items = BorrowRequestItem::query()
                ->where('borrow_request_id', $lockedRequest->id)
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cancel' => 'ไม่พบรายการอุปกรณ์ในคำขอนี้ กรุณาติดต่อผู้ดูแลระบบ',
                ]);
            }

            $equipment = Equipment::query()
                ->whereKey($items->pluck('equipment_id'))
                ->lockForUpdate()
                ->get();
            $previousStatus = $lockedRequest->status;
            $previousItemStatuses = $items
                ->mapWithKeys(fn (BorrowRequestItem $item): array => [
                    (string) $item->id => $item->status->value,
                ])
                ->all();
            $previousEquipmentStatuses = $equipment
                ->mapWithKeys(fn (Equipment $item): array => [
                    (string) $item->id => $item->status->value,
                ])
                ->all();

            BorrowRequestItem::query()
                ->where('borrow_request_id', $lockedRequest->id)
                ->update(['status' => BorrowItemStatus::Cancelled->value]);
            $lockedRequest->update(['status' => BorrowRequestStatus::Cancelled]);

            if (in_array($previousStatus, [
                BorrowRequestStatus::Approved,
                BorrowRequestStatus::ReadyForPickup,
            ], true)) {
                $remainingReservationEquipmentIds = BorrowRequestItem::query()
                    ->whereIn('equipment_id', $items->pluck('equipment_id'))
                    ->where('borrow_request_id', '!=', $lockedRequest->id)
                    ->whereHas('borrowRequest', function ($query): void {
                        $query->whereIn('status', [
                            BorrowRequestStatus::Approved->value,
                            BorrowRequestStatus::ReadyForPickup->value,
                            BorrowRequestStatus::Borrowed->value,
                            BorrowRequestStatus::Overdue->value,
                        ]);
                    })
                    ->distinct()
                    ->pluck('equipment_id');
                $releasableEquipmentIds = $equipment
                    ->filter(fn (Equipment $item): bool => $item->status === EquipmentStatus::Reserved)
                    ->pluck('id')
                    ->diff($remainingReservationEquipmentIds);

                if ($releasableEquipmentIds->isNotEmpty()) {
                    Equipment::query()
                        ->whereKey($releasableEquipmentIds)
                        ->update(['status' => EquipmentStatus::Available->value]);
                }
            }

            $currentEquipmentStatuses = Equipment::query()
                ->whereKey($items->pluck('equipment_id'))
                ->get()
                ->mapWithKeys(fn (Equipment $item): array => [
                    (string) $item->id => $item->status->value,
                ])
                ->all();

            $this->auditLogger->record(
                $borrower,
                'borrow.cancelled',
                $lockedRequest,
                [
                    'status' => $previousStatus->value,
                    'item_statuses' => $previousItemStatuses,
                    'equipment_statuses' => $previousEquipmentStatuses,
                ],
                [
                    'status' => BorrowRequestStatus::Cancelled->value,
                    'item_status' => BorrowItemStatus::Cancelled->value,
                    'equipment_statuses' => $currentEquipmentStatuses,
                ],
            );

            return $lockedRequest->refresh();
        });
    }
}
