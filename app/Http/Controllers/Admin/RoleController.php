<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Administration\CreateRole;
use App\Actions\Administration\DeleteRole;
use App\Actions\Administration\UpdateRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\Administration\RoleAdministrationQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(RoleAdministrationQueryService $roles): View
    {
        return view('admin.roles.index', $roles->index());
    }

    public function create(RoleAdministrationQueryService $roles): View
    {
        return view('admin.roles.form', $roles->createForm());
    }

    public function store(StoreRoleRequest $request, CreateRole $createRole): RedirectResponse
    {
        $role = $createRole->execute(
            $request->user(),
            $request->validated('name'),
            $request->validated('permissions', []),
        );

        return to_route('admin.roles')->with('success', "สร้างบทบาท {$role->name} เรียบร้อยแล้ว");
    }

    public function edit(Role $role, RoleAdministrationQueryService $roles): View
    {
        return view('admin.roles.form', $roles->editForm($role));
    }

    public function update(UpdateRoleRequest $request, Role $role, UpdateRole $updateRole): RedirectResponse
    {
        $role = $updateRole->execute(
            $request->user(),
            $role,
            $request->validated('name'),
            $request->validated('permissions', []),
        );

        return to_route('admin.roles')->with('success', "อัปเดตบทบาท {$role->name} เรียบร้อยแล้ว");
    }

    public function destroy(DeleteRoleRequest $request, Role $role, DeleteRole $deleteRole): RedirectResponse
    {
        $deleteRole->execute($request->user(), $role);

        return to_route('admin.roles')->with('success', 'ลบบทบาทเรียบร้อยแล้ว');
    }
}
