<?php

use App\Models\User;
use App\Services\Authorization\RoleSimulationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('lets a super administrator simulate another role without changing persisted roles', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    $this->actingAs($superAdmin)
        ->post(route('admin.role-simulation.store'), ['role' => 'Borrower'])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas(RoleSimulationService::SESSION_KEY, 'Borrower');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('โหมดจำลองบทบาท ผู้ยืม')
        ->assertSee('data-dashboard-role="borrower"', false)
        ->assertSee('ติดตามการยืมของคุณได้ในที่เดียว')
        ->assertSee('data-menu-key="borrow.mine"', false)
        ->assertDontSee('data-menu-key="admin.users"', false);

    $this->get(route('admin.users'))->assertForbidden();

    expect($superAdmin->fresh()->getRoleNames()->all())->toBe(['SuperAdmin']);

    $this->delete(route('admin.role-simulation.destroy'))
        ->assertRedirect(route('dashboard'))
        ->assertSessionMissing(RoleSimulationService::SESSION_KEY);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-menu-key="admin.users"', false)
        ->assertDontSee('โหมดจำลองบทบาท ผู้ยืม');

    $this->assertDatabaseHas('audit_logs', ['event' => 'role_simulation.started']);
    $this->assertDatabaseHas('audit_logs', ['event' => 'role_simulation.stopped']);
});

it('lets a super administrator switch simulated roles while simulation is active', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    $this->actingAs($superAdmin)
        ->withSession([RoleSimulationService::SESSION_KEY => 'Borrower'])
        ->post(route('admin.role-simulation.store'), ['role' => 'Approver'])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas(RoleSimulationService::SESSION_KEY, 'Approver');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('โหมดจำลองบทบาท ผู้อนุมัติ')
        ->assertSee('data-dashboard-role="approver"', false)
        ->assertSee('พิจารณาคำขอได้อย่างรวดเร็วและชัดเจน')
        ->assertSee('data-menu-key="approval.index"', false)
        ->assertDontSee('data-menu-key="borrow.mine"', false);

    $this->assertDatabaseHas('audit_logs', ['event' => 'role_simulation.changed']);
});

it('rejects role simulation changes from users who are not super administrators', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');

    $this->actingAs($borrower)
        ->post(route('admin.role-simulation.store'), ['role' => 'Admin'])
        ->assertForbidden();

    $this->withSession([RoleSimulationService::SESSION_KEY => 'Admin'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionMissing(RoleSimulationService::SESSION_KEY)
        ->assertDontSee('โหมดจำลองบทบาท');
});

it('validates the role selected for simulation', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    $this->actingAs($superAdmin)
        ->post(route('admin.role-simulation.store'), ['role' => 'SuperAdmin'])
        ->assertSessionHasErrors('role')
        ->assertSessionMissing(RoleSimulationService::SESSION_KEY);

    $this->post(route('admin.role-simulation.store'), ['role' => 'MissingRole'])
        ->assertSessionHasErrors('role')
        ->assertSessionMissing(RoleSimulationService::SESSION_KEY);
});
