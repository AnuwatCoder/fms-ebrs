<?php

namespace App\Console\Commands;

use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use Illuminate\Console\Command;

class MarkOverdueBorrowRequests extends Command
{
    protected $signature = 'borrow:mark-overdue';

    protected $description = 'Mark borrowed requests past their expected return date as overdue';

    public function handle(): int
    {
        $updated = BorrowRequest::query()
            ->where('status', BorrowRequestStatus::Borrowed->value)
            ->whereDate('expected_return_date', '<', today())
            ->update(['status' => BorrowRequestStatus::Overdue->value]);

        $this->info("Marked {$updated} borrow request(s) as overdue.");

        return self::SUCCESS;
    }
}
