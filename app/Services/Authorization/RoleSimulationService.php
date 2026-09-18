<?php

namespace App\Services\Authorization;

use App\Models\User;
use App\Support\Authorization\CoreRoles;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

final class RoleSimulationService
{
    public const SESSION_KEY = 'auth.role_simulation';

    public const REQUEST_ATTRIBUTE = 'role_simulation';

    public function apply(Request $request): void
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return;
        }

        if (! $this->isActualSuperAdmin($user)) {
            $request->session()->forget(self::SESSION_KEY);

            return;
        }

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'SuperAdmin')
            ->orderBy('name')
            ->get();
        $activeRoleName = $request->session()->get(self::SESSION_KEY);
        $activeRole = is_string($activeRoleName)
            ? $roles->firstWhere('name', $activeRoleName)
            : null;

        if ($activeRoleName !== null && $activeRole === null) {
            $request->session()->forget(self::SESSION_KEY);
        }

        if ($activeRole !== null) {
            $activeRole->loadMissing('permissions');
            $user->setRelation('roles', collect([$activeRole]));
            $user->setRelation('permissions', collect());
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE, [
            'allowed' => true,
            'active' => $activeRole?->name,
            'active_label' => $activeRole === null ? null : CoreRoles::label($activeRole->name),
            'roles' => $roles
                ->map(fn (Role $role): array => [
                    'name' => $role->name,
                    'label' => CoreRoles::label($role->name),
                ])
                ->values()
                ->all(),
        ]);
    }

    private function isActualSuperAdmin(User $user): bool
    {
        return $user->roles()
            ->where('name', 'SuperAdmin')
            ->where('guard_name', 'web')
            ->exists();
    }
}
