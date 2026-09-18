<?php

namespace App\Services\Borrowing;

use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BorrowRequestQueryService
{
    public function __construct(private EquipmentAvailabilityService $availability) {}

    /** @return array<string, mixed> */
    public function createForm(): array
    {
        return [
            'equipment' => $this->availability->availableBetween(
                today(),
                today()->addDays((int) SystemSetting::read('default_loan_days')),
            ),
            'defaultLoanDays' => (int) SystemSetting::read('default_loan_days'),
            'maxItems' => (int) SystemSetting::read('max_items_per_request'),
            'borrowingTerms' => config('borrowing.terms'),
        ];
    }

    /** @param array<string, mixed> $filters */
    public function mine(User $user, array $filters): array
    {
        return $this->listing(
            BorrowRequest::query()->where('user_id', $user->id),
            $filters,
            true,
        );
    }

    /** @param array<string, mixed> $filters */
    public function all(array $filters): array
    {
        return $this->listing(BorrowRequest::query(), $filters, false);
    }

    /**
     * @param  Builder<BorrowRequest>  $query
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function listing(Builder $query, array $filters, bool $mine): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = isset($filters['status']) ? BorrowRequestStatus::from($filters['status']) : null;
        $borrowRequests = $query
            ->with('borrower:id,name')
            ->withCount('items')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('request_no', 'like', "%{$search}%")
                        ->orWhere('purpose', 'like', "%{$search}%")
                        ->orWhereHas('borrower', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status, fn (Builder $query): Builder => $query->where('status', $status->value))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return [
            'borrowRequests' => $borrowRequests,
            'statuses' => BorrowRequestStatus::cases(),
            'mine' => $mine,
        ];
    }
}
