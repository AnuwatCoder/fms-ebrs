<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Administration\UpdateRoleSimulation;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartRoleSimulationRequest;
use App\Http\Requests\StopRoleSimulationRequest;
use App\Support\Authorization\CoreRoles;
use Illuminate\Http\RedirectResponse;

class RoleSimulationController extends Controller
{
    public function store(
        StartRoleSimulationRequest $request,
        UpdateRoleSimulation $updateRoleSimulation,
    ): RedirectResponse {
        $roleName = $request->validated('role');
        $updateRoleSimulation->start($request->user(), $request->session(), $roleName);

        return to_route('dashboard')
            ->with('status', 'กำลังจำลองมุมมองบทบาท '.CoreRoles::label($roleName));
    }

    public function destroy(
        StopRoleSimulationRequest $request,
        UpdateRoleSimulation $updateRoleSimulation,
    ): RedirectResponse {
        $updateRoleSimulation->stop($request->user(), $request->session());

        return to_route('dashboard')->with('success', 'กลับสู่มุมมอง SuperAdmin แล้ว');
    }
}
