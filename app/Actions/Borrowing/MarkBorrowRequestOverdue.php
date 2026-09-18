<?php

namespace App\Actions\Borrowing;

use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use App\Services\Notifications\BestEffortNotificationDispatcher;
use App\Services\Notifications\BorrowRequestNotificationService;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;

class MarkBorrowRequestOverdue
{
    public function __construct(
        private AuditLogger $auditLogger,
        private BorrowRequestNotificationService $notifications,
        private BestEffortNotificationDispatcher $notificationDispatcher,
    ) {}

    public function execute(BorrowRequest $borrowRequest): bool
    {
        $updatedRequest = DB::transaction(function () use ($borrowRequest): ?BorrowRequest {
            $lockedRequest = BorrowRequest::query()
                ->whereKey($borrowRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedRequest->status !== BorrowRequestStatus::Borrowed
                || ! $lockedRequest->expected_return_date->isBefore(today())
            ) {
                return null;
            }

            $lockedRequest->update(['status' => BorrowRequestStatus::Overdue]);
            $this->auditLogger->record(
                null,
                'borrow.marked_overdue',
                $lockedRequest,
                ['status' => BorrowRequestStatus::Borrowed->value],
                ['status' => BorrowRequestStatus::Overdue->value],
            );

            return $lockedRequest->refresh();
        });

        if ($updatedRequest === null) {
            return false;
        }

        $this->notificationDispatcher->dispatch(
            fn (): mixed => $this->notifications->notifyOverdue($updatedRequest),
        );

        return true;
    }
}
