<?php

namespace App\Actions\Checkout;

use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequest;
use App\Models\BorrowRequestItem;
use App\Models\Equipment;
use App\Models\EquipmentCheckout;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcessEquipmentCheckout
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function markReady(User $staff, BorrowRequest $borrowRequest): BorrowRequest
    {
        return DB::transaction(function () use ($staff, $borrowRequest): BorrowRequest {
            $lockedRequest = $this->lockedRequest($borrowRequest);

            if ($lockedRequest->status !== BorrowRequestStatus::Approved) {
                throw ValidationException::withMessages([
                    'action' => 'คำขอนี้ไม่อยู่ในสถานะที่เตรียมจ่ายได้',
                ]);
            }

            $lockedRequest->update(['status' => BorrowRequestStatus::ReadyForPickup]);
            $lockedRequest->items()->update(['status' => BorrowItemStatus::ReadyForPickup->value]);

            $this->auditLogger->record(
                $staff,
                'checkout.ready',
                $lockedRequest,
                ['status' => BorrowRequestStatus::Approved->value],
                ['status' => BorrowRequestStatus::ReadyForPickup->value],
            );

            return $lockedRequest->refresh();
        });
    }

    /** @param array<int|string, string> $conditions */
    public function checkout(
        User $staff,
        BorrowRequest $borrowRequest,
        array $conditions,
        ?string $note = null,
    ): BorrowRequest {
        return DB::transaction(function () use ($staff, $borrowRequest, $conditions, $note): BorrowRequest {
            $lockedRequest = $this->lockedRequest($borrowRequest);

            if ($lockedRequest->status !== BorrowRequestStatus::ReadyForPickup) {
                throw ValidationException::withMessages([
                    'action' => 'คำขอนี้ยังไม่พร้อมสำหรับจ่ายอุปกรณ์',
                ]);
            }

            $items = BorrowRequestItem::query()
                ->where('borrow_request_id', $lockedRequest->id)
                ->lockForUpdate()
                ->get();
            $normalizedConditions = collect($conditions)->mapWithKeys(
                fn (string $condition, int|string $itemId): array => [(int) $itemId => trim($condition)],
            );

            if (
                $items->isEmpty()
                || $items->contains(fn (BorrowRequestItem $item): bool => ! $normalizedConditions->has($item->id))
                || $items->contains(fn (BorrowRequestItem $item): bool => $item->status !== BorrowItemStatus::ReadyForPickup)
                || EquipmentCheckout::query()->whereIn('borrow_request_item_id', $items->pluck('id'))->exists()
            ) {
                throw ValidationException::withMessages([
                    'conditions' => 'รายการอุปกรณ์ไม่พร้อมจ่ายหรือเคยถูกจ่ายไปแล้ว กรุณาตรวจสอบอีกครั้ง',
                ]);
            }

            $equipment = Equipment::query()
                ->whereKey($items->pluck('equipment_id'))
                ->lockForUpdate()
                ->get();

            if ($equipment->count() !== $items->count() || $equipment->contains(
                fn (Equipment $item): bool => ! $item->active || $item->status !== EquipmentStatus::Reserved,
            )) {
                throw ValidationException::withMessages([
                    'action' => 'มีอุปกรณ์บางรายการไม่อยู่ในสถานะจอง กรุณาตรวจสอบอีกครั้ง',
                ]);
            }

            foreach ($items as $item) {
                $item->checkout()->create([
                    'checked_out_by' => $staff->id,
                    'checked_out_at' => now(),
                    'condition_before' => $normalizedConditions->get($item->id),
                    'note' => $note,
                ]);
            }

            BorrowRequestItem::query()
                ->where('borrow_request_id', $lockedRequest->id)
                ->update(['status' => BorrowItemStatus::Borrowed->value]);
            Equipment::query()
                ->whereKey($items->pluck('equipment_id'))
                ->update(['status' => EquipmentStatus::Borrowed->value]);
            $lockedRequest->update(['status' => BorrowRequestStatus::Borrowed]);

            $this->auditLogger->record(
                $staff,
                'checkout.processed',
                $lockedRequest,
                ['status' => BorrowRequestStatus::ReadyForPickup->value],
                [
                    'status' => BorrowRequestStatus::Borrowed->value,
                    'equipment_ids' => $items->pluck('equipment_id')->all(),
                ],
            );

            return $lockedRequest->refresh();
        });
    }

    private function lockedRequest(BorrowRequest $borrowRequest): BorrowRequest
    {
        return BorrowRequest::query()
            ->whereKey($borrowRequest->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }
}
