<?php

namespace App\Actions\Administration;

use App\Models\User;
use App\Services\Authorization\PrivilegedAccessService;
use App\Support\Auditing\AuditLogger;
use App\Support\Authorization\CoreRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateRole
{
    public function __construct(
        private AuditLogger $auditLogger,
        private PrivilegedAccessService $privilegedAccess,
    ) {}

    /** @param list<int> $permissionIds */
    public function execute(User $actor, Role $role, string $name, array $permissionIds): Role
    {
        return DB::transaction(function () use ($actor, $role, $name, $permissionIds): Role {
            $lockedRole = Role::query()
                ->whereKey($role->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (CoreRoles::contains($lockedRole->name) && $name !== $lockedRole->name) {
                throw ValidationException::withMessages([
                    'name' => 'ไม่สามารถเปลี่ยนชื่อบทบาทหลักของระบบได้',
                ]);
            }

            $oldValues = [
                'name' => $lockedRole->name,
                'permissions' => $lockedRole->permissions()->pluck('name')->all(),
            ];
            $permissions = Permission::query()
                ->whereKey($permissionIds)
                ->where('guard_name', 'web')
                ->get();

            $this->privilegedAccess->assertCanUpdateRole($actor, $lockedRole, $permissions);

            $lockedRole->update(['name' => $name]);
            $lockedRole->syncPermissions($permissions);

            $this->auditLogger->record($actor, 'role.updated', $lockedRole, $oldValues, [
                'name' => $lockedRole->name,
                'permissions' => $permissions->pluck('name')->all(),
            ]);

            return $lockedRole->refresh();
        });
    }
}
