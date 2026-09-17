<?php

namespace App\Actions\Administration;

use App\Models\User;
use App\Support\Auditing\AuditLogger;
use App\Support\Authorization\CoreRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, Role $role): void
    {
        DB::transaction(function () use ($actor, $role): void {
            $lockedRole = Role::query()
                ->whereKey($role->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (CoreRoles::contains($lockedRole->name) || $lockedRole->users()->exists()) {
                throw ValidationException::withMessages([
                    'role' => 'ไม่สามารถลบบทบาทหลักหรือบทบาทที่ยังมีผู้ใช้งานได้',
                ]);
            }

            $oldValues = [
                'name' => $lockedRole->name,
                'permissions' => $lockedRole->permissions()->pluck('name')->all(),
            ];

            $this->auditLogger->record($actor, 'role.deleted', $lockedRole, $oldValues);
            $lockedRole->delete();
        });
    }
}
