<?php

namespace App\Actions\Administration;

use App\Models\User;
use App\Services\Authorization\RoleSimulationService;
use App\Support\Auditing\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UpdateRoleSimulation
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function start(User $actor, Session $session, string $roleName): void
    {
        $this->ensureSuperAdmin($actor);

        $role = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->where('name', '!=', 'SuperAdmin')
            ->firstOrFail();
        $previousRole = $session->get(RoleSimulationService::SESSION_KEY);

        DB::transaction(function () use ($actor, $session, $role, $previousRole): void {
            $session->put(RoleSimulationService::SESSION_KEY, $role->name);

            $this->auditLogger->record(
                $actor,
                $previousRole === null ? 'role_simulation.started' : 'role_simulation.changed',
                null,
                $previousRole === null ? [] : ['role' => $previousRole],
                ['role' => $role->name],
            );
        });
    }

    public function stop(User $actor, Session $session): void
    {
        $this->ensureSuperAdmin($actor);

        DB::transaction(function () use ($actor, $session): void {
            $previousRole = $session->pull(RoleSimulationService::SESSION_KEY);

            if (is_string($previousRole)) {
                $this->auditLogger->record(
                    $actor,
                    'role_simulation.stopped',
                    null,
                    ['role' => $previousRole],
                );
            }
        });
    }

    private function ensureSuperAdmin(User $actor): void
    {
        if (! $actor->roles()->where('name', 'SuperAdmin')->where('guard_name', 'web')->exists()) {
            throw new AuthorizationException;
        }
    }
}
