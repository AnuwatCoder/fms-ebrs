<?php

namespace App\Policies;

use App\Models\BorrowRequest;
use App\Models\User;

class BorrowRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('borrow.view');
    }

    public function create(User $user): bool
    {
        return $user->can('borrow.create');
    }

    public function view(User $user, BorrowRequest $borrowRequest): bool
    {
        return $user->can('borrow.view')
            || ($user->can('borrow.view-own') && $borrowRequest->user_id === $user->id);
    }

    public function approve(User $user, BorrowRequest $borrowRequest): bool
    {
        return $user->can('approval.approve');
    }

    public function reject(User $user, BorrowRequest $borrowRequest): bool
    {
        return $user->can('approval.reject');
    }

    public function checkout(User $user, BorrowRequest $borrowRequest): bool
    {
        return $user->can('checkout.process');
    }

    public function receiveReturn(User $user, BorrowRequest $borrowRequest): bool
    {
        return $user->can('return.process');
    }

    public function cancel(User $user, BorrowRequest $borrowRequest): bool
    {
        return $user->can('borrow.cancel') && $borrowRequest->user_id === $user->id;
    }
}
