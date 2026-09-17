<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(): View
    {
        return view('admin.permissions.index', [
            'permissions' => Permission::query()
                ->where('guard_name', 'web')
                ->with('roles:id,name')
                ->orderBy('name')
                ->get()
                ->groupBy(fn (Permission $permission): string => str($permission->name)->before('.')->toString()),
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
