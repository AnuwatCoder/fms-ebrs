<?php

namespace App\Services\Notifications;

use App\Models\BorrowRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\BorrowRequestApprovedNotification;
use App\Notifications\NewBorrowRequestNotification;
use Illuminate\Database\Eloquent\Builder;

class BorrowRequestNotificationService
{
    public function notifyReviewers(BorrowRequest $borrowRequest): void
    {
        if (! SystemSetting::read('email_notifications_enabled')) {
            return;
        }

        $borrowRequest->loadMissing('borrower:id,name')->loadCount('items');

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
        if (! SystemSetting::read('email_notifications_enabled')) {
            return;
        }

        $borrowRequest->loadMissing('borrower:id,name,email')->loadCount('items');
        $borrower = $borrowRequest->borrower;

        if ($borrower !== null && filled($borrower->email)) {
            $borrower->notify(new BorrowRequestApprovedNotification(
                $borrowRequest,
                (int) $borrowRequest->items_count,
            ));
        }
    }
}
