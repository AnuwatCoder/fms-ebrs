<?php

namespace App\Services\Operations;

use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\ReturnStatus;
use App\Models\BorrowRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OperationsQueueService
{
    public function approvals(): LengthAwarePaginator
    {
        return BorrowRequest::query()
            ->with([
                'borrower:id,name,email',
                'items:id,borrow_request_id,equipment_id',
                'items.equipment:id,category_id,equipment_code,name,status',
                'items.equipment.category:id,name',
            ])
            ->where('status', BorrowRequestStatus::Pending->value)
            ->oldest('submitted_at')
            ->paginate(10);
    }

    public function checkouts(): LengthAwarePaginator
    {
        return BorrowRequest::query()
            ->with([
                'borrower:id,name,email',
                'items:id,borrow_request_id,equipment_id,status',
                'items.equipment:id,category_id,equipment_code,name,status,location',
                'items.equipment.category:id,name',
            ])
            ->whereIn('status', [
                BorrowRequestStatus::Approved->value,
                BorrowRequestStatus::ReadyForPickup->value,
            ])
            ->orderBy('borrow_date')
            ->paginate(10);
    }

    /** @return array<string, mixed> */
    public function returns(): array
    {
        return [
            'borrowRequests' => BorrowRequest::query()
                ->with([
                    'borrower:id,name,email',
                    'items' => fn ($query) => $query->where('status', BorrowItemStatus::Borrowed->value),
                    'items.equipment:id,category_id,equipment_code,name,status,location',
                    'items.equipment.category:id,name',
                    'items.checkout:id,borrow_request_item_id,condition_before,checked_out_at',
                ])
                ->whereIn('status', [
                    BorrowRequestStatus::Borrowed->value,
                    BorrowRequestStatus::Overdue->value,
                ])
                ->orderBy('expected_return_date')
                ->paginate(10),
            'returnStatuses' => ReturnStatus::cases(),
        ];
    }
}
