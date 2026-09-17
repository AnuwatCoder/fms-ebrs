<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /** @var list<string> */
    private const PERMISSIONS = [
        'equipment.view',
        'equipment.create',
        'equipment.update',
        'equipment.delete',
        'category.view',
        'category.create',
        'category.update',
        'category.delete',
        'borrow.view',
        'borrow.view-own',
        'borrow.create',
        'borrow.cancel',
        'approval.view',
        'approval.approve',
        'approval.reject',
        'checkout.view',
        'checkout.process',
        'return.view',
        'return.process',
        'maintenance.view',
        'maintenance.manage',
        'report.equipment',
        'report.borrowing',
        'report.overdue',
        'report.damage',
        'user.manage',
        'role.manage',
        'permission.manage',
        'settings.manage',
        'audit.view',
    ];

    /** @var array<string, list<string>> */
    private const ROLE_PERMISSIONS = [
        'Admin' => [
            'equipment.view', 'equipment.create', 'equipment.update', 'equipment.delete',
            'category.view', 'category.create', 'category.update', 'category.delete',
            'borrow.view',
            'approval.view',
            'checkout.view', 'checkout.process',
            'return.view', 'return.process',
            'maintenance.view', 'maintenance.manage',
            'report.equipment', 'report.borrowing', 'report.overdue', 'report.damage',
            'audit.view',
        ],
        'Approver' => [
            'borrow.view',
            'approval.view', 'approval.approve', 'approval.reject',
        ],
        'Staff' => [
            'equipment.view',
            'borrow.view',
            'checkout.view', 'checkout.process',
            'return.view', 'return.process',
            'maintenance.view', 'maintenance.manage',
        ],
        'Borrower' => [
            'equipment.view',
            'borrow.view-own', 'borrow.create', 'borrow.cancel',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('SuperAdmin', 'web');
        $superAdmin->syncPermissions(Permission::query()->where('guard_name', 'web')->get());

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
