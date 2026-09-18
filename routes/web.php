<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoleSimulationController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\AuthentikController;
use App\Http\Controllers\BorrowApprovalController;
use App\Http\Controllers\BorrowRequestAvailabilityController;
use App\Http\Controllers\BorrowRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentCategoryController;
use App\Http\Controllers\EquipmentCheckoutController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentReturnController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? to_route('dashboard')
        : to_route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::get('/auth/authentik/redirect', [AuthentikController::class, 'redirect'])
        ->middleware('throttle:authentik')
        ->name('auth.authentik.redirect');
    Route::get('/auth/authentik/callback', [AuthentikController::class, 'callback'])
        ->middleware('throttle:authentik')
        ->name('auth.authentik.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/equipment', [EquipmentController::class, 'index'])
        ->middleware('permission:equipment.view')
        ->name('equipment.index');
    Route::get('/equipment/create', [EquipmentController::class, 'create'])
        ->middleware('permission:equipment.create')
        ->name('equipment.create');
    Route::post('/equipment', [EquipmentController::class, 'store'])
        ->middleware('permission:equipment.create')
        ->name('equipment.store');
    Route::get('/equipment/{equipment}', [EquipmentController::class, 'show'])
        ->middleware(['permission:equipment.view', 'can:view,equipment'])
        ->name('equipment.show');
    Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit'])
        ->middleware(['permission:equipment.update', 'can:update,equipment'])
        ->name('equipment.edit');
    Route::patch('/equipment/{equipment}', [EquipmentController::class, 'update'])
        ->middleware('permission:equipment.update')
        ->name('equipment.update');
    Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])
        ->middleware('permission:equipment.delete')
        ->name('equipment.destroy');

    Route::get('/categories', [EquipmentCategoryController::class, 'index'])
        ->middleware('permission:category.view')
        ->name('category.index');
    Route::get('/categories/create', [EquipmentCategoryController::class, 'create'])
        ->middleware('permission:category.create')
        ->name('category.create');
    Route::post('/categories', [EquipmentCategoryController::class, 'store'])
        ->middleware('permission:category.create')
        ->name('category.store');
    Route::get('/categories/{category}/edit', [EquipmentCategoryController::class, 'edit'])
        ->middleware(['permission:category.update', 'can:update,category'])
        ->name('category.edit');
    Route::patch('/categories/{category}', [EquipmentCategoryController::class, 'update'])
        ->middleware('permission:category.update')
        ->name('category.update');
    Route::delete('/categories/{category}', [EquipmentCategoryController::class, 'destroy'])
        ->middleware('permission:category.delete')
        ->name('category.destroy');

    Route::get('/maintenance', [MaintenanceController::class, 'index'])
        ->middleware('permission:maintenance.view')
        ->name('maintenance.index');
    Route::get('/maintenance/incidents/create', [MaintenanceController::class, 'create'])
        ->middleware('permission:maintenance.manage')
        ->name('maintenance.create');
    Route::post('/maintenance/incidents', [MaintenanceController::class, 'store'])
        ->middleware('permission:maintenance.manage')
        ->name('maintenance.store');
    Route::get('/maintenance/incidents/{incident}/resolve', [MaintenanceController::class, 'resolve'])
        ->middleware(['permission:maintenance.manage', 'can:resolve,incident'])
        ->name('maintenance.resolve');
    Route::patch('/maintenance/incidents/{incident}/resolve', [MaintenanceController::class, 'update'])
        ->middleware('permission:maintenance.manage')
        ->name('maintenance.update');

    Route::get('/borrow-requests/create', [BorrowRequestController::class, 'create'])
        ->middleware('permission:borrow.create')
        ->name('borrow.create');
    Route::get('/borrow-requests/availability', BorrowRequestAvailabilityController::class)
        ->middleware('permission:borrow.create')
        ->name('borrow.availability');
    Route::post('/borrow-requests', [BorrowRequestController::class, 'store'])
        ->middleware('permission:borrow.create')
        ->name('borrow.store');
    Route::get('/borrow-requests/mine', [BorrowRequestController::class, 'mine'])
        ->middleware('permission:borrow.view-own')
        ->name('borrow.mine');
    Route::patch('/borrow-requests/{borrowRequest}/cancel', [BorrowRequestController::class, 'cancel'])
        ->middleware(['permission:borrow.cancel', 'can:cancel,borrowRequest'])
        ->name('borrow.cancel');
    Route::get('/borrow-requests', [BorrowRequestController::class, 'index'])
        ->middleware('permission:borrow.view')
        ->name('borrow.index');

    Route::get('/approvals', [BorrowApprovalController::class, 'index'])
        ->middleware('permission:approval.view')
        ->name('approval.index');
    Route::patch('/approvals/{borrowRequest}', [BorrowApprovalController::class, 'update'])
        ->middleware('permission:approval.view')
        ->name('approval.update');

    Route::get('/checkouts', [EquipmentCheckoutController::class, 'index'])
        ->middleware('permission:checkout.view')
        ->name('checkout.index');
    Route::patch('/checkouts/{borrowRequest}', [EquipmentCheckoutController::class, 'update'])
        ->middleware('permission:checkout.process')
        ->name('checkout.update');

    Route::get('/returns', [EquipmentReturnController::class, 'index'])
        ->middleware('permission:return.view')
        ->name('return.index');
    Route::patch('/returns/{borrowRequest}', [EquipmentReturnController::class, 'update'])
        ->middleware('permission:return.process')
        ->name('return.update');

    Route::prefix('reports')->group(function () {
        Route::get('/equipment', [ReportController::class, 'equipment'])
            ->middleware('permission:report.equipment')
            ->name('report.equipment');
        Route::get('/borrowing', [ReportController::class, 'borrowing'])
            ->middleware('permission:report.borrowing')
            ->name('report.borrowing');
        Route::get('/overdue', [ReportController::class, 'overdue'])
            ->middleware('permission:report.overdue')
            ->name('report.overdue');
        Route::get('/damage', [ReportController::class, 'damage'])
            ->middleware('permission:report.damage')
            ->name('report.damage');
    });

    Route::prefix('admin')->group(function () {
        Route::post('/role-simulation', [RoleSimulationController::class, 'store'])
            ->name('admin.role-simulation.store');
        Route::delete('/role-simulation', [RoleSimulationController::class, 'destroy'])
            ->name('admin.role-simulation.destroy');

        Route::get('/users', [UserController::class, 'index'])
            ->middleware('permission:user.manage')
            ->name('admin.users');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->middleware(['permission:user.manage', 'can:update,user'])
            ->name('admin.users.edit');
        Route::patch('/users/{user}', [UserController::class, 'update'])
            ->middleware('permission:user.manage')
            ->name('admin.users.update');

        Route::get('/roles', [RoleController::class, 'index'])
            ->middleware('permission:role.manage')
            ->name('admin.roles');
        Route::get('/roles/create', [RoleController::class, 'create'])
            ->middleware('permission:role.manage')
            ->name('admin.roles.create');
        Route::post('/roles', [RoleController::class, 'store'])
            ->middleware('permission:role.manage')
            ->name('admin.roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->middleware(['permission:role.manage', 'can:update,role'])
            ->name('admin.roles.edit');
        Route::patch('/roles/{role}', [RoleController::class, 'update'])
            ->middleware('permission:role.manage')
            ->name('admin.roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:role.manage')
            ->name('admin.roles.destroy');

        Route::get('/permissions', [PermissionController::class, 'index'])
            ->middleware('permission:permission.manage')
            ->name('admin.permissions');
        Route::get('/settings', [SystemSettingController::class, 'index'])
            ->middleware('permission:settings.manage')
            ->name('admin.settings');
        Route::patch('/settings', [SystemSettingController::class, 'update'])
            ->middleware('permission:settings.manage')
            ->name('admin.settings.update');
    });

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('permission:audit.view')
        ->name('audit.index');

    Route::post('/logout', [AuthentikController::class, 'logout'])->name('logout');
});
