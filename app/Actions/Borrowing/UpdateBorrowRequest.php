<?php

namespace App\Actions\Borrowing;

use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\User;
use App\Services\Borrowing\EquipmentAvailabilityService;
use App\Services\Notifications\BestEffortNotificationDispatcher;
use App\Services\Notifications\BorrowRequestNotificationService;
use App\Support\Auditing\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBorrowRequest
{
    public function __construct(
        private AuditLogger $auditLogger,
        private EquipmentAvailabilityService $availability,
        private BorrowRequestNotificationService $notifications,
        private BestEffortNotificationDispatcher $notificationDispatcher,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function execute(
        User $borrower,
        BorrowRequest $borrowRequest,
        array $attributes,
        bool $submit,
    ): BorrowRequest {
        $updatedRequest = DB::transaction(function () use ($borrower, $borrowRequest, $attributes, $submit): BorrowRequest {
            $lockedRequest = BorrowRequest::query()
                ->whereKey($borrowRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->user_id !== $borrower->id) {
                throw new AuthorizationException;
            }

            if ($lockedRequest->status !== BorrowRequestStatus::Draft) {
                throw ValidationException::withMessages([
                    'intent' => 'แก้ไขได้เฉพาะคำขอที่ยังเป็นฉบับร่างเท่านั้น',
                ]);
            }

            if ($submit && ! filter_var($attributes['accept_terms'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                throw ValidationException::withMessages([
                    'accept_terms' => 'กรุณายอมรับข้อตกลงและเงื่อนไขการยืมก่อนส่งคำขอ',
                ]);
            }

            $equipmentIds = array_values(array_unique($attributes['equipment_ids']));
            $lockedEquipment = Equipment::query()
                ->whereKey($equipmentIds)
                ->lockForUpdate()
                ->get(['id', 'active', 'status']);

            if (
                $lockedEquipment->count() !== count($equipmentIds)
                || $lockedEquipment->contains(
                    fn (Equipment $equipment): bool => ! $equipment->active
                        || ! in_array($equipment->status->value, $this->availability->schedulableEquipmentStatuses(), true),
                )
                || $this->availability->hasConflicts(
                    $equipmentIds,
                    Carbon::parse($attributes['borrow_date']),
                    Carbon::parse($attributes['expected_return_date']),
                    excludeBorrowRequestId: $lockedRequest->id,
                )
            ) {
                throw ValidationException::withMessages([
                    'equipment_ids' => 'มีอุปกรณ์บางรายการไม่ว่างในช่วงวันที่เลือก กรุณาตรวจสอบและเลือกรายการใหม่',
                ]);
            }

            $oldValues = [
                'purpose' => $lockedRequest->purpose,
                'borrow_date' => $lockedRequest->borrow_date->toDateString(),
                'expected_return_date' => $lockedRequest->expected_return_date->toDateString(),
                'equipment_ids' => $lockedRequest->items()->pluck('equipment_id')->all(),
                'status' => $lockedRequest->status->value,
            ];
            $nextStatus = $submit ? BorrowRequestStatus::Pending : BorrowRequestStatus::Draft;
            $nextItemStatus = $submit ? BorrowItemStatus::Pending : BorrowItemStatus::Draft;

            $lockedRequest->update([
                'purpose' => $attributes['purpose'],
                'usage_location' => $attributes['usage_location'] ?? null,
                'borrow_date' => $attributes['borrow_date'],
                'expected_return_date' => $attributes['expected_return_date'],
                'note' => $attributes['note'] ?? null,
                'status' => $nextStatus,
                'submitted_at' => $submit ? now() : null,
                'terms_accepted_at' => $submit ? now() : null,
                'terms_version' => $submit ? (string) config('borrowing.terms.version') : null,
            ]);

            $lockedRequest->items()->delete();
            $lockedRequest->items()->createMany(array_map(
                fn (int $equipmentId): array => [
                    'equipment_id' => $equipmentId,
                    'status' => $nextItemStatus,
                ],
                $equipmentIds,
            ));

            $this->auditLogger->record(
                $borrower,
                $submit ? 'borrow.submitted' : 'borrow.draft.updated',
                $lockedRequest,
                $oldValues,
                [
                    'purpose' => $lockedRequest->purpose,
                    'borrow_date' => $lockedRequest->borrow_date->toDateString(),
                    'expected_return_date' => $lockedRequest->expected_return_date->toDateString(),
                    'equipment_ids' => $equipmentIds,
                    'status' => $nextStatus->value,
                    'terms_version' => $lockedRequest->terms_version,
                ],
            );

            return $lockedRequest->refresh();
        });

        if ($submit) {
            $this->notificationDispatcher->dispatch(
                fn (): mixed => $this->notifications->notifyReviewers($updatedRequest),
            );
        }

        return $updatedRequest;
    }
}
