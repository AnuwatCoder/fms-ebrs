<?php

namespace App\Actions\Administration;

use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateRole
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param list<int> $permissionIds */
    public function execute(User $actor, string $name, array $permissionIds): Role
    {
        return DB::transaction(function () use ($actor, $name, $permissionIds): Role {
            $role = Role::query()->create([
                'name' => $name,
                'guard_name' => 'web',
            ]);
            $permissions = Permission::query()
                ->whereKey($permissionIds)
                ->where('guard_name', 'web')
                ->get();
            $role->syncPermissions($permissions);

            $this->auditLogger->record($actor, 'role.created', $role, [], [
                'name' => $role->name,
                'permissions' => $permissions->pluck('name')->all(),
            ]);

            return $role;
        });
    }
}
