<?php

namespace App\Services\Administration;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class UserAdministrationQueryService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function index(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $roleId = (int) ($filters['role'] ?? 0);
        $active = $filters['active'] ?? null;

        return [
            'users' => User::query()
                ->with('roles:id,name')
                ->when($search !== '', fn (Builder $query): Builder => $query->where(
                    fn (Builder $query): Builder => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%"),
                ))
                ->when(
                    $roleId > 0,
                    fn (Builder $query): Builder => $query->whereHas(
                        'roles',
                        fn (Builder $query): Builder => $query->whereKey($roleId),
                    ),
                )
                ->when(
                    in_array($active, ['0', '1'], true),
                    fn (Builder $query): Builder => $query->where('active', $active === '1'),
                )
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'roles' => $this->roles(),
        ];
    }

    /** @return array<string, mixed> */
    public function edit(User $user): array
    {
        return [
            'managedUser' => $user->load('roles:id,name'),
            'roles' => $this->roles(),
        ];
    }

    /** @return Collection<int, Role> */
    private function roles(): Collection
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
