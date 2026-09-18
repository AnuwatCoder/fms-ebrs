<?php

namespace App\Console\Commands;

use App\Actions\Borrowing\MarkBorrowRequestOverdue;
use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use Illuminate\Console\Command;

class MarkOverdueBorrowRequests extends Command
{
    protected $signature = 'borrow:mark-overdue';

    protected $description = 'Mark borrowed requests past their expected return date as overdue';

    public function handle(MarkBorrowRequestOverdue $markOverdue): int
    {
        $updated = 0;

        BorrowRequest::query()
            ->where('status', BorrowRequestStatus::Borrowed->value)
            ->whereDate('expected_return_date', '<', today())
            ->select(['id'])
            ->eachById(function (BorrowRequest $borrowRequest) use ($markOverdue, &$updated): void {
                if ($markOverdue->execute($borrowRequest)) {
                    $updated++;
                }
            });

        $this->info("Marked {$updated} borrow request(s) as overdue.");

        return self::SUCCESS;
    }
}
