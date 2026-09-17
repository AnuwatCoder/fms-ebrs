<?php

namespace App\Services\Administration;

use App\Support\Authorization\CoreRoles;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAdministrationQueryService
{
    /** @return array<string, mixed> */
    public function index(): array
    {
        return [
            'roles' => Role::query()
                ->where('guard_name', 'web')
                ->withCount(['permissions', 'users'])
                ->orderBy('name')
                ->get(),
            'coreRoles' => CoreRoles::NAMES,
        ];
    }

    /** @return array<string, mixed> */
    public function createForm(): array
    {
        return [
            'role' => new Role(['guard_name' => 'web']),
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissions' => collect(),
            'coreRoles' => CoreRoles::NAMES,
        ];
    }

    /** @return array<string, mixed> */
    public function editForm(Role $role): array
    {
        abort_unless($role->guard_name === 'web', 404);

        return [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissions' => $role->permissions()->pluck('permissions.id'),
            'coreRoles' => CoreRoles::NAMES,
        ];
    }

    /** @return Collection<string, Collection<int, Permission>> */
    private function permissionGroups(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => str($permission->name)->before('.')->toString());
    }
}
