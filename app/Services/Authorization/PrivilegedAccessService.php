<?php

namespace App\Services\Authorization;

use App\Models\User;
use App\Support\Authorization\CoreRoles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PrivilegedAccessService
{
    public function isActualSuperAdmin(User $user): bool
    {
        return $user->roles()
            ->where('name', 'SuperAdmin')
            ->where('guard_name', 'web')
            ->exists();
    }

    /** @param Collection<int, Role> $roles */
    public function assertCanAssignRoles(User $actor, User $managedUser, Collection $roles): void
    {
        $this->assertAbility($actor, 'user.manage');

        if ($this->isActualSuperAdmin($actor)) {
            return;
        }

        if ($this->isActualSuperAdmin($managedUser) || $roles->contains('name', 'SuperAdmin')) {
            throw ValidationException::withMessages([
                'roles' => 'เฉพาะ SuperAdmin เท่านั้นที่สามารถจัดการบัญชีหรือมอบหมายบทบาท SuperAdmin ได้',
            ]);
        }

        $permissions = $roles
            ->flatMap(fn (Role $role): Collection => $role->permissions)
            ->unique('id');

        $this->assertWithinPermissionCeiling($actor, $permissions, 'roles');
    }

    /** @param Collection<int, Permission> $permissions */
    public function assertCanCreateRole(User $actor, Collection $permissions): void
    {
        $this->assertAbility($actor, 'role.manage');

        if ($this->isActualSuperAdmin($actor)) {
            return;
        }

        $this->assertWithinPermissionCeiling($actor, $permissions, 'permissions');
    }

    /** @param Collection<int, Permission> $permissions */
    public function assertCanUpdateRole(User $actor, Role $role, Collection $permissions): void
    {
        $this->assertAbility($actor, 'role.manage');

        if ($this->isActualSuperAdmin($actor)) {
            return;
        }

        if (CoreRoles::contains($role->name)) {
            throw ValidationException::withMessages([
                'role' => 'เฉพาะ SuperAdmin เท่านั้นที่สามารถแก้ไขบทบาทหลักของระบบได้',
            ]);
        }

        $this->assertWithinPermissionCeiling($actor, $permissions, 'permissions');
    }

    /** @param Collection<int, Permission> $permissions */
    private function assertWithinPermissionCeiling(User $actor, Collection $permissions, string $field): void
    {
        $actorPermissionIds = $actor->getAllPermissions()->pluck('id');

        if ($permissions->pluck('id')->diff($actorPermissionIds)->isEmpty()) {
            return;
        }

        throw ValidationException::withMessages([
            $field => 'ไม่สามารถมอบหมายสิทธิ์ที่สูงกว่าสิทธิ์ของผู้ดำเนินการได้',
        ]);
    }

    private function assertAbility(User $actor, string $ability): void
    {
        if (! $actor->can($ability)) {
            throw new AuthorizationException;
        }
    }
}
