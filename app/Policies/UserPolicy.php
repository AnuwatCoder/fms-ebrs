<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.manage');
    }

    public function update(User $user, User $managedUser): bool
    {
        return $user->can('user.manage');
    }
}
