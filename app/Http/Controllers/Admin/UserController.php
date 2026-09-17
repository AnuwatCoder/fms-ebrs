<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Administration\UpdateManagedUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManagedUserIndexRequest;
use App\Http\Requests\UpdateManagedUserRequest;
use App\Models\User;
use App\Services\Administration\UserAdministrationQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(
        ManagedUserIndexRequest $request,
        UserAdministrationQueryService $users,
    ): View {
        return view('admin.users.index', $users->index($request->validated()));
    }

    public function edit(User $user, UserAdministrationQueryService $users): View
    {
        return view('admin.users.edit', $users->edit($user));
    }

    public function update(
        UpdateManagedUserRequest $request,
        User $user,
        UpdateManagedUser $updateManagedUser,
    ): RedirectResponse {
        $validated = $request->validated();
        $updateManagedUser->execute(
            $request->user(),
            $user,
            (bool) $validated['active'],
            $validated['roles'] ?? [],
        );

        return to_route('admin.users')->with('success', "อัปเดตผู้ใช้ {$user->name} เรียบร้อยแล้ว");
    }
}
