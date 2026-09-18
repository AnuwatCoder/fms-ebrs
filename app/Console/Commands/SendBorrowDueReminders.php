<?php

namespace App\Console\Commands;

use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use App\Models\SystemSetting;
use App\Services\Notifications\BorrowRequestNotificationService;
use Illuminate\Console\Command;
use Throwable;

class SendBorrowDueReminders extends Command
{
    protected $signature = 'borrow:send-due-reminders';

    protected $description = 'Send internal notifications for borrow requests approaching their return date';

    public function handle(BorrowRequestNotificationService $notifications): int
    {
        $days = (int) SystemSetting::read('overdue_alert_days');
        $sent = 0;

        BorrowRequest::query()
            ->where('status', BorrowRequestStatus::Borrowed->value)
            ->whereDate('expected_return_date', today()->addDays($days))
            ->with('borrower:id,name')
            ->select(['id', 'request_no', 'user_id', 'expected_return_date'])
            ->eachById(function (BorrowRequest $borrowRequest) use ($days, $notifications, &$sent): void {
                try {
                    if ($notifications->notifyDueSoon($borrowRequest, $days)) {
                        $sent++;
                    }
                } catch (Throwable $exception) {
                    report($exception);
                }
            });

        $this->info("Sent {$sent} due reminder(s).");

        return self::SUCCESS;
    }
}
