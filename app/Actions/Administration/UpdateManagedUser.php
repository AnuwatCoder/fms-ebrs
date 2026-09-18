<?php

namespace App\Actions\Administration;

use App\Models\User;
use App\Services\Authentication\UserSessionRevoker;
use App\Services\Authorization\PrivilegedAccessService;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UpdateManagedUser
{
    public function __construct(
        private AuditLogger $auditLogger,
        private PrivilegedAccessService $privilegedAccess,
        private UserSessionRevoker $sessionRevoker,
    ) {}

    /** @param list<int> $roleIds */
    public function execute(User $actor, User $managedUser, bool $active, array $roleIds): User
    {
        $updatedUser = DB::transaction(function () use ($actor, $managedUser, $active, $roleIds): User {
            $lockedUser = User::query()
                ->whereKey($managedUser->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $roles = Role::query()
                ->whereKey($roleIds)
                ->where('guard_name', 'web')
                ->with('permissions:id,name')
                ->get();

            if ($actor->is($lockedUser) && ! $active) {
                throw ValidationException::withMessages([
                    'active' => 'ไม่สามารถระงับบัญชีของตนเองได้',
                ]);
            }

            $removesActiveSuperAdmin = $lockedUser->hasRole('SuperAdmin')
                && (! $active || ! $roles->contains('name', 'SuperAdmin'));

            if ($removesActiveSuperAdmin) {
                Role::query()
                    ->where('name', 'SuperAdmin')
                    ->where('guard_name', 'web')
                    ->lockForUpdate()
                    ->firstOrFail();

                $superAdminCount = User::query()
                    ->where('active', true)
                    ->role('SuperAdmin')
                    ->count();

                if ($superAdminCount <= 1) {
                    throw ValidationException::withMessages([
                        ($active ? 'roles' : 'active') => 'ระบบต้องมี SuperAdmin ที่เปิดใช้งานอย่างน้อย 1 คน',
                    ]);
                }
            }

            $this->privilegedAccess->assertCanAssignRoles($actor, $lockedUser, $roles);

            $oldValues = [
                'active' => $lockedUser->active,
                'roles' => $lockedUser->getRoleNames()->all(),
            ];

            $lockedUser->update(['active' => $active]);
            $lockedUser->syncRoles($roles);

            $this->auditLogger->record($actor, 'user.updated', $lockedUser, $oldValues, [
                'active' => $lockedUser->active,
                'roles' => $roles->pluck('name')->all(),
            ]);

            return $lockedUser->refresh();
        });

        if (! $updatedUser->active) {
            $this->sessionRevoker->revoke($updatedUser);
        }

        return $updatedUser;
    }
}
