<?php

namespace App\Actions\Borrowing;

use App\Enums\ApprovalAction;
use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequest;
use App\Models\BorrowRequestItem;
use App\Models\Equipment;
use App\Models\User;
use App\Services\Borrowing\EquipmentAvailabilityService;
use App\Services\Notifications\BorrowRequestNotificationService;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcessBorrowApproval
{
    public function __construct(
        private AuditLogger $auditLogger,
        private EquipmentAvailabilityService $availability,
        private BorrowRequestNotificationService $notifications,
    ) {}

    public function execute(
        User $approver,
        BorrowRequest $borrowRequest,
        ApprovalAction $action,
        ?string $comment = null,
    ): BorrowRequest {
        $processedRequest = DB::transaction(function () use ($approver, $borrowRequest, $action, $comment): BorrowRequest {
            $lockedRequest = BorrowRequest::query()
                ->whereKey($borrowRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status !== BorrowRequestStatus::Pending) {
                throw ValidationException::withMessages([
                    'action' => 'คำขอนี้ได้รับการดำเนินการไปแล้ว',
                ]);
            }

            $items = BorrowRequestItem::query()
                ->where('borrow_request_id', $lockedRequest->id)
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty() || $items->contains(
                fn (BorrowRequestItem $item): bool => $item->status !== BorrowItemStatus::Pending,
            )) {
                throw ValidationException::withMessages([
                    'action' => 'รายการอุปกรณ์ในคำขอไม่อยู่ในสถานะรออนุมัติ',
                ]);
            }

            if ($action === ApprovalAction::Approved) {
                $equipment = Equipment::query()
                    ->whereKey($items->pluck('equipment_id'))
                    ->lockForUpdate()
                    ->get();

                $unavailable = $equipment->filter(
                    fn (Equipment $item): bool => ! $item->active
                        || ! in_array($item->status->value, $this->availability->schedulableEquipmentStatuses(), true),
                );

                if (
                    $equipment->count() !== $items->count()
                    || $unavailable->isNotEmpty()
                    || $this->availability->hasConflicts(
                        $items->pluck('equipment_id')->all(),
                        $lockedRequest->borrow_date,
                        $lockedRequest->expected_return_date,
                        $this->availability->approvedRequestBlockingStatuses(),
                        $lockedRequest->id,
                    )
                ) {
                    throw ValidationException::withMessages([
                        'action' => 'ไม่สามารถอนุมัติได้ เนื่องจากมีอุปกรณ์บางรายการไม่พร้อมให้ยืม',
                    ]);
                }

                Equipment::query()
                    ->whereKey($items->pluck('equipment_id'))
                    ->update(['status' => EquipmentStatus::Reserved->value]);

                BorrowRequestItem::query()
                    ->where('borrow_request_id', $lockedRequest->id)
                    ->update(['status' => BorrowItemStatus::Reserved->value]);

                $nextStatus = BorrowRequestStatus::Approved;
            } else {
                BorrowRequestItem::query()
                    ->where('borrow_request_id', $lockedRequest->id)
                    ->update(['status' => BorrowItemStatus::Rejected->value]);

                $nextStatus = BorrowRequestStatus::Rejected;
            }

            $lockedRequest->update(['status' => $nextStatus]);
            $lockedRequest->approvals()->create([
                'approver_id' => $approver->id,
                'action' => $action,
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $this->auditLogger->record(
                $approver,
                $action === ApprovalAction::Approved ? 'approval.approved' : 'approval.rejected',
                $lockedRequest,
                ['status' => BorrowRequestStatus::Pending->value],
                [
                    'status' => $nextStatus->value,
                    'comment' => $comment,
                ],
            );

            return $lockedRequest->refresh();
        });

        if ($action === ApprovalAction::Approved) {
            $this->notifications->notifyApprovedBorrower($processedRequest);
        }

        return $processedRequest;
    }
}
