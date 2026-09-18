<?php

use App\Actions\Administration\UpdateManagedUser;
use App\Enums\ApprovalAction;
use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\User;
use App\Services\Notifications\BorrowRequestNotificationService;
use Database\Seeders\EquipmentCategorySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        RolePermissionSeeder::class,
        EquipmentCategorySeeder::class,
    ]);
});

it('adds browser security headers to web responses', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertHeader('Content-Security-Policy', config('security.content_security_policy'))
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');
});

it('terminates an authenticated session when the account is inactive', function () {
    $user = User::factory()->create(['active' => false]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
    $this->assertDatabaseHas('audit_logs', [
        'event' => 'auth.session.revoked',
        'causer_id' => $user->id,
    ]);
});

it('prevents even a super administrator from approving their own request', function () {
    $user = User::factory()->create();
    $user->assignRole('SuperAdmin');
    $category = EquipmentCategory::query()->where('code', 'OTHER')->firstOrFail();
    $equipment = Equipment::query()->create([
        'category_id' => $category->id,
        'equipment_code' => 'EQ-SELF-APPROVAL',
        'name' => 'Self approval test',
        'status' => EquipmentStatus::Available,
        'active' => true,
    ]);
    $borrowRequest = BorrowRequest::query()->create([
        'request_no' => 'BR-SELF-APPROVAL',
        'user_id' => $user->id,
        'purpose' => 'Self approval security test',
        'borrow_date' => today(),
        'expected_return_date' => today()->addDay(),
        'status' => BorrowRequestStatus::Pending,
        'submitted_at' => now(),
    ]);
    $borrowRequest->items()->create([
        'equipment_id' => $equipment->id,
        'status' => BorrowItemStatus::Pending,
    ]);

    $this->actingAs($user)
        ->patch(route('approval.update', $borrowRequest), [
            'action' => ApprovalAction::Approved->value,
        ])
        ->assertForbidden();

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Pending)
        ->and($borrowRequest->approvals()->exists())->toBeFalse()
        ->and($equipment->refresh()->status)->toBe(EquipmentStatus::Available);
});

it('prevents a delegated user manager from assigning the super administrator role', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo('user.manage');
    $managedUser = User::factory()->create();
    $superAdminRole = Role::findByName('SuperAdmin');

    $this->actingAs($manager)
        ->patch(route('admin.users.update', $managedUser), [
            'active' => '1',
            'roles' => [$superAdminRole->id],
        ])
        ->assertSessionHasErrors('roles');

    expect($managedUser->refresh()->hasRole('SuperAdmin'))->toBeFalse();
});

it('prevents a delegated role manager from granting permissions they do not hold', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo('role.manage');
    $elevatedPermission = Permission::findByName('user.manage');

    $this->actingAs($manager)
        ->post(route('admin.roles.store'), [
            'name' => 'Escalated Role',
            'permissions' => [$elevatedPermission->id],
        ])
        ->assertSessionHasErrors('permissions');

    expect(Role::query()->where('name', 'Escalated Role')->exists())->toBeFalse();
});

it('revokes database sessions when an administrator deactivates an account', function () {
    config(['session.driver' => 'database']);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');
    $managedUser = User::factory()->create();
    $managedUser->assignRole('Borrower');
    $borrowerRole = Role::findByName('Borrower');

    DB::table('sessions')->insert([
        'id' => 'managed-user-session',
        'user_id' => $managedUser->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Security test',
        'payload' => 'test-payload',
        'last_activity' => now()->timestamp,
    ]);

    app(UpdateManagedUser::class)->execute(
        $superAdmin,
        $managedUser,
        false,
        [$borrowerRole->id],
    );

    expect($managedUser->refresh()->active)->toBeFalse();
    $this->assertDatabaseMissing('sessions', ['id' => 'managed-user-session']);
});

it('keeps a committed borrow submission successful when notification delivery fails', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $category = EquipmentCategory::query()->where('code', 'OTHER')->firstOrFail();
    $equipment = Equipment::query()->create([
        'category_id' => $category->id,
        'equipment_code' => 'EQ-NOTIFICATION-FAILURE',
        'name' => 'Notification failure test',
        'status' => EquipmentStatus::Available,
        'active' => true,
    ]);
    $notifications = Mockery::mock(BorrowRequestNotificationService::class);
    $notifications->shouldReceive('notifyReviewers')
        ->once()
        ->andThrow(new RuntimeException('Notification channel unavailable'));
    $this->app->instance(BorrowRequestNotificationService::class, $notifications);

    $this->actingAs($borrower)
        ->post(route('borrow.store'), [
            'purpose' => 'Notification failure security test',
            'borrow_date' => today()->format('Y-m-d'),
            'expected_return_date' => today()->addDay()->format('Y-m-d'),
            'equipment_ids' => [$equipment->id],
            'accept_terms' => '1',
        ])
        ->assertRedirect(route('borrow.mine'));

    $this->assertDatabaseHas('borrow_requests', [
        'user_id' => $borrower->id,
        'status' => BorrowRequestStatus::Pending->value,
    ]);
    $this->assertDatabaseHas('audit_logs', ['event' => 'borrow.submitted']);
});
