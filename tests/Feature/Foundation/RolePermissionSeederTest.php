<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('seeds the required roles and granular permissions idempotently', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);

    expect(Role::query()->pluck('name')->all())
        ->toEqualCanonicalizing(['SuperAdmin', 'Admin', 'Approver', 'Staff', 'Borrower'])
        ->and(Permission::query()->where('name', 'checkout.process')->exists())->toBeTrue()
        ->and(Permission::query()->where('name', 'permission.manage')->exists())->toBeTrue();

    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');

    expect($borrower->can('borrow.create'))->toBeTrue()
        ->and($borrower->can('approval.approve'))->toBeFalse();
});

it('grants super administrators every gate ability', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('SuperAdmin');

    expect($user->can('an.ability.added.in.the.future'))->toBeTrue();
});
