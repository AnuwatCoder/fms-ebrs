<?php

namespace App\Services\Notifications;

use App\Models\BorrowRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\BorrowRequestApprovedNotification;
use App\Notifications\BorrowRequestInternalNotification;
use App\Notifications\NewBorrowRequestNotification;
use Illuminate\Database\Eloquent\Builder;

class BorrowRequestNotificationService
{
    public function notifyReviewers(BorrowRequest $borrowRequest): void
    {
        $borrowRequest->loadMissing('borrower:id,name')->loadCount('items');

        User::permission('approval.view')
            ->where('active', true)
            ->eachById(function (User $user) use ($borrowRequest): void {
                $this->notifyInternal(
                    $user,
                    $borrowRequest,
                    'borrow.submitted',
                    'มีคำขอยืมใหม่รอตรวจสอบ',
                    "{$borrowRequest->request_no} จาก {$borrowRequest->borrower->name}",
                    'clipboard-clock',
                    'warning',
                );
            });

        if (! SystemSetting::read('email_notifications_enabled')) {
            return;
        }

        User::query()
            ->where('active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereHas('roles', function (Builder $query): void {
                $query
                    ->whereIn('name', ['SuperAdmin', 'Admin'])
                    ->where('guard_name', 'web');
            })
            ->eachById(function (User $user) use ($borrowRequest): void {
                $user->notify(new NewBorrowRequestNotification(
                    $borrowRequest,
                    (int) $borrowRequest->items_count,
                ));
            });
    }

    public function notifyApprovedBorrower(BorrowRequest $borrowRequest): void
    {
        $borrowRequest->loadMissing('borrower:id,name,email')->loadCount('items');
        $borrower = $borrowRequest->borrower;

        if ($borrower === null) {
            return;
        }

        $this->notifyInternal(
            $borrower,
            $borrowRequest,
            'borrow.approved',
            'คำขอยืมได้รับการอนุมัติแล้ว',
            "คำขอ {$borrowRequest->request_no} ได้รับการอนุมัติแล้ว",
            'badge-check',
            'success',
        );

        if (SystemSetting::read('email_notifications_enabled') && filled($borrower->email)) {
            $borrower->notify(new BorrowRequestApprovedNotification(
                $borrowRequest,
                (int) $borrowRequest->items_count,
            ));
        }
    }

    public function notifyRejectedBorrower(BorrowRequest $borrowRequest, ?string $comment): void
    {
        $borrowRequest->loadMissing('borrower:id,name');

        if ($borrowRequest->borrower === null) {
            return;
        }

        $message = "คำขอ {$borrowRequest->request_no} ไม่ได้รับการอนุมัติ";
        if (filled($comment)) {
            $message .= ': '.$comment;
        }

        $this->notifyInternal(
            $borrowRequest->borrower,
            $borrowRequest,
            'borrow.rejected',
            'คำขอยืมไม่ได้รับการอนุมัติ',
            $message,
            'circle-x',
            'danger',
        );
    }

    public function notifyReadyForPickup(BorrowRequest $borrowRequest): void
    {
        $borrowRequest->loadMissing('borrower:id,name');

        if ($borrowRequest->borrower !== null) {
            $this->notifyInternal(
                $borrowRequest->borrower,
                $borrowRequest,
                'borrow.ready_for_pickup',
                'อุปกรณ์พร้อมรับแล้ว',
                "คำขอ {$borrowRequest->request_no} พร้อมรับอุปกรณ์แล้ว",
                'package-check',
                'info',
            );
        }
    }

    public function notifyReturned(BorrowRequest $borrowRequest): void
    {
        $borrowRequest->loadMissing('borrower:id,name');

        if ($borrowRequest->borrower !== null) {
            $this->notifyInternal(
                $borrowRequest->borrower,
                $borrowRequest,
                'borrow.returned',
                'รับคืนอุปกรณ์เรียบร้อยแล้ว',
                "คำขอ {$borrowRequest->request_no} รับคืนอุปกรณ์ครบแล้ว",
                'package-open',
                'success',
            );
        }
    }

    public function notifyDueSoon(BorrowRequest $borrowRequest, int $days): bool
    {
        $borrowRequest->loadMissing('borrower:id,name');
        $borrower = $borrowRequest->borrower;

        if ($borrower === null || $this->wasAlreadyNotified($borrower, $borrowRequest, 'borrow.due_soon')) {
            return false;
        }

        $dayText = $days === 0 ? 'วันนี้' : "ในอีก {$days} วัน";
        $this->notifyInternal(
            $borrower,
            $borrowRequest,
            'borrow.due_soon',
            'ใกล้ถึงกำหนดคืนอุปกรณ์',
            "คำขอ {$borrowRequest->request_no} มีกำหนดคืน{$dayText}",
            'alarm-clock',
            'warning',
        );

        return true;
    }

    public function notifyOverdue(BorrowRequest $borrowRequest): void
    {
        $borrowRequest->loadMissing('borrower:id,name');

        if ($borrowRequest->borrower !== null) {
            $this->notifyInternal(
                $borrowRequest->borrower,
                $borrowRequest,
                'borrow.overdue',
                'คำขอยืมเกินกำหนดคืน',
                "คำขอ {$borrowRequest->request_no} เกินกำหนดคืนแล้ว กรุณาติดต่อเจ้าหน้าที่",
                'triangle-alert',
                'danger',
            );
        }
    }

    private function notifyInternal(
        User $user,
        BorrowRequest $borrowRequest,
        string $event,
        string $title,
        string $message,
        string $icon,
        string $tone,
    ): void {
        $user->notify(new BorrowRequestInternalNotification(
            event: $event,
            borrowRequestId: $borrowRequest->id,
            requestNo: $borrowRequest->request_no,
            title: $title,
            message: $message,
            icon: $icon,
            tone: $tone,
        ));
    }

    private function wasAlreadyNotified(User $user, BorrowRequest $borrowRequest, string $event): bool
    {
        return $user->notifications()
            ->where('type', BorrowRequestInternalNotification::class)
            ->where('data->event', $event)
            ->where('data->borrow_request_id', $borrowRequest->id)
            ->exists();
    }
}
