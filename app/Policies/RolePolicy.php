<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Authorization\PrivilegedAccessService;
use App\Support\Authorization\CoreRoles;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function __construct(private PrivilegedAccessService $privilegedAccess) {}

    public function viewAny(User $user): bool
    {
        return $user->can('role.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('role.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('role.manage')
            && $role->guard_name === 'web'
            && (! CoreRoles::contains($role->name) || $this->privilegedAccess->isActualSuperAdmin($user));
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('role.manage') && $role->guard_name === 'web';
    }
}
