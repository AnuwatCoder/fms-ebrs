<?php

use App\Enums\ApprovalAction;
use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Enums\IncidentType;
use App\Enums\ReturnStatus;
use App\Http\Requests\ProcessEquipmentCheckoutRequest;
use App\Models\AuditLog;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\EquipmentCategorySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingSeeder;
use Database\Seeders\WorkflowStatusSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        RolePermissionSeeder::class,
        EquipmentCategorySeeder::class,
        SystemSettingSeeder::class,
    ]);
});

function operationsUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function operationsEquipment(string $code, EquipmentStatus $status): Equipment
{
    $category = EquipmentCategory::query()->where('code', 'OTHER')->firstOrFail();

    return Equipment::query()->create([
        'category_id' => $category->id,
        'equipment_code' => $code,
        'name' => $code,
        'status' => $status,
        'active' => true,
    ]);
}

function operationsRequest(
    User $borrower,
    string $number,
    BorrowRequestStatus $status,
    array $equipment,
): BorrowRequest {
    $borrowRequest = BorrowRequest::query()->create([
        'request_no' => $number,
        'user_id' => $borrower->id,
        'purpose' => 'Operations test',
        'borrow_date' => today()->subDay(),
        'expected_return_date' => today()->addDay(),
        'status' => $status,
        'submitted_at' => now()->subDay(),
    ]);

    $itemStatus = match ($status) {
        BorrowRequestStatus::Approved => BorrowItemStatus::Reserved,
        BorrowRequestStatus::ReadyForPickup => BorrowItemStatus::ReadyForPickup,
        BorrowRequestStatus::Borrowed, BorrowRequestStatus::Overdue => BorrowItemStatus::Borrowed,
        default => BorrowItemStatus::Pending,
    };

    foreach ($equipment as $item) {
        $borrowRequest->items()->create([
            'equipment_id' => $item->id,
            'status' => $itemStatus,
        ]);
    }

    return $borrowRequest;
}

it('seeds every workflow enum status idempotently', function () {
    $this->seed(WorkflowStatusSeeder::class);

    foreach (EquipmentStatus::cases() as $status) {
        expect(Equipment::query()->where('status', $status->value)->exists())->toBeTrue();
    }
    foreach (BorrowRequestStatus::cases() as $status) {
        expect(BorrowRequest::query()->where('status', $status->value)->exists())->toBeTrue();
    }
    foreach (BorrowItemStatus::cases() as $status) {
        $this->assertDatabaseHas('borrow_request_items', ['status' => $status->value]);
    }
    foreach (ApprovalAction::cases() as $action) {
        $this->assertDatabaseHas('borrow_approvals', ['action' => $action->value]);
    }
    foreach (ReturnStatus::cases() as $status) {
        $this->assertDatabaseHas('equipment_returns', ['return_status' => $status->value]);
    }
    foreach (IncidentType::cases() as $type) {
        $this->assertDatabaseHas('equipment_incidents', ['type' => $type->value]);
    }

    $counts = [
        Equipment::query()->count(),
        BorrowRequest::query()->count(),
        AuditLog::query()->count(),
    ];
    $this->seed(WorkflowStatusSeeder::class);

    expect([
        Equipment::query()->count(),
        BorrowRequest::query()->count(),
        AuditLog::query()->count(),
    ])->toBe($counts);
});

it('renders every completed menu for a super administrator', function () {
    $this->seed(WorkflowStatusSeeder::class);
    $superAdmin = operationsUser('SuperAdmin');

    $routes = [
        'category.index',
        'maintenance.index',
        'approval.index',
        'checkout.index',
        'return.index',
        'report.equipment',
        'report.borrowing',
        'report.overdue',
        'report.damage',
        'admin.users',
        'admin.roles',
        'admin.permissions',
        'admin.settings',
        'audit.index',
    ];

    Model::preventLazyLoading();

    try {
        foreach ($routes as $routeName) {
            $this->actingAs($superAdmin)
                ->get(route($routeName))
                ->assertOk();
        }
    } finally {
        Model::preventLazyLoading(false);
    }
});

it('renders operation queues as tables with modal actions', function () {
    $operator = operationsUser('SuperAdmin');
    $borrower = User::factory()->create();

    $approvalRequest = operationsRequest(
        $borrower,
        'BR-MODAL-APPROVAL',
        BorrowRequestStatus::Pending,
        [operationsEquipment('MODAL-APPROVAL', EquipmentStatus::Available)],
    );
    $checkoutRequest = operationsRequest(
        $borrower,
        'BR-MODAL-CHECKOUT',
        BorrowRequestStatus::Approved,
        [operationsEquipment('MODAL-CHECKOUT', EquipmentStatus::Reserved)],
    );
    $returnRequest = operationsRequest(
        $borrower,
        'BR-MODAL-RETURN',
        BorrowRequestStatus::Borrowed,
        [operationsEquipment('MODAL-RETURN', EquipmentStatus::Borrowed)],
    );

    $this->actingAs($operator)
        ->get(route('approval.index'))
        ->assertOk()
        ->assertSee('data-operation-table="approvals"', false)
        ->assertSee('data-bs-target="#approval-modal-'.$approvalRequest->id.'"', false)
        ->assertSee('data-operation-modal="approval"', false)
        ->assertSee('data-operation-form="approval"', false);

    $this->actingAs($operator)
        ->get(route('checkout.index'))
        ->assertOk()
        ->assertSee('data-operation-table="checkouts"', false)
        ->assertSee('data-bs-target="#checkout-modal-'.$checkoutRequest->id.'"', false)
        ->assertSee('data-operation-modal="checkout"', false)
        ->assertSee('data-operation-form="checkout"', false);

    $this->actingAs($operator)
        ->get(route('return.index'))
        ->assertOk()
        ->assertSee('data-operation-table="returns"', false)
        ->assertSee('data-bs-target="#return-modal-'.$returnRequest->id.'"', false)
        ->assertSee('data-operation-modal="return"', false)
        ->assertSee('data-operation-form="return"', false);
});

it('manages equipment categories and protects categories that are in use', function () {
    $admin = operationsUser('Admin');

    $this->actingAs($admin)
        ->get(route('category.create'))
        ->assertOk()
        ->assertSee('data-auto-code="category"', false)
        ->assertDontSee('name="code"', false);

    $this->actingAs($admin)
        ->post(route('category.store'), [
            'name' => 'เครื่องมือห้องปฏิบัติการ',
            'description' => 'อุปกรณ์สำหรับห้องปฏิบัติการ',
            'active' => '1',
        ])
        ->assertRedirect(route('category.index'));

    $category = EquipmentCategory::query()->where('name', 'เครื่องมือห้องปฏิบัติการ')->firstOrFail();
    $generatedCode = $category->code;

    expect($generatedCode)->toMatch('/^CAT-\d{4}$/');
    $this->assertDatabaseHas('audit_logs', ['event' => 'category.created']);

    $this->actingAs($admin)
        ->get(route('category.edit', $category))
        ->assertOk();

    $this->actingAs($admin)
        ->patch(route('category.update', $category), [
            'name' => 'เครื่องมือแล็บ',
            'code' => 'TAMPERED-CODE',
            'description' => null,
            'active' => '0',
        ])
        ->assertRedirect(route('category.index'));

    expect($category->refresh()->name)->toBe('เครื่องมือแล็บ')
        ->and($category->code)->toBe($generatedCode)
        ->and($category->active)->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('category.destroy', $category))
        ->assertRedirect(route('category.index'));

    expect(EquipmentCategory::withTrashed()->findOrFail($category->id)->trashed())->toBeTrue();

    $usedCategory = EquipmentCategory::query()->where('code', 'OTHER')->firstOrFail();
    operationsEquipment('CATEGORY-IN-USE', EquipmentStatus::Available);

    $this->actingAs($admin)
        ->delete(route('category.destroy', $usedCategory))
        ->assertSessionHasErrors('category');

    expect($usedCategory->fresh())->not->toBeNull();
});

it('reports and resolves equipment incidents with synchronized equipment status', function () {
    $staff = operationsUser('Staff');
    $equipment = operationsEquipment('MAINTENANCE-001', EquipmentStatus::Available);

    $this->actingAs($staff)
        ->get(route('maintenance.create'))
        ->assertOk();

    $this->actingAs($staff)
        ->post(route('maintenance.store'), [
            'equipment_id' => $equipment->id,
            'type' => IncidentType::Maintenance->value,
            'description' => 'พัดลมระบายความร้อนมีเสียงดังผิดปกติ',
        ])
        ->assertRedirect(route('maintenance.index'));

    $incident = $equipment->incidents()->firstOrFail();
    expect($equipment->refresh()->status)->toBe(EquipmentStatus::Maintenance)
        ->and($incident->resolved_at)->toBeNull();
    $this->assertDatabaseHas('audit_logs', ['event' => 'maintenance.reported']);

    $this->actingAs($staff)
        ->post(route('maintenance.store'), [
            'equipment_id' => $equipment->id,
            'type' => IncidentType::Damaged->value,
            'description' => 'เหตุซ้ำที่ต้องไม่ถูกบันทึก',
        ])
        ->assertSessionHasErrors('equipment_id');

    expect($equipment->incidents()->count())->toBe(1);

    $this->actingAs($staff)
        ->get(route('maintenance.resolve', $incident))
        ->assertOk();

    $this->actingAs($staff)
        ->patch(route('maintenance.update', $incident), [
            'resolution' => 'เปลี่ยนพัดลมและทดสอบการทำงานเรียบร้อย',
            'equipment_status' => EquipmentStatus::Available->value,
        ])
        ->assertRedirect(route('maintenance.index'));

    expect($incident->refresh()->resolved_at)->not->toBeNull()
        ->and($incident->resolved_by)->toBe($staff->id)
        ->and($equipment->refresh()->status)->toBe(EquipmentStatus::Available);
    $this->assertDatabaseHas('audit_logs', ['event' => 'maintenance.resolved']);

    $this->actingAs($staff)
        ->get(route('maintenance.resolve', $incident))
        ->assertForbidden();
});

it('denies category and maintenance management to borrowers', function () {
    $borrower = operationsUser('Borrower');
    $equipment = operationsEquipment('FORBIDDEN-MAINTENANCE', EquipmentStatus::Available);

    $this->actingAs($borrower)
        ->get(route('category.index'))
        ->assertForbidden();

    $this->actingAs($borrower)
        ->get(route('maintenance.index'))
        ->assertForbidden();

    $this->actingAs($borrower)
        ->post(route('maintenance.store'), [
            'equipment_id' => $equipment->id,
            'type' => IncidentType::Damaged->value,
            'description' => 'Unauthorized',
        ])
        ->assertForbidden();

    expect($equipment->incidents()->exists())->toBeFalse();
});

it('enforces ownership and privileged workflow policies', function () {
    $borrower = operationsUser('Borrower');
    $otherBorrower = operationsUser('Borrower');
    $equipment = operationsEquipment('POLICY-001', EquipmentStatus::Available);
    $ownRequest = operationsRequest(
        $borrower,
        'BR-POLICY-OWN',
        BorrowRequestStatus::Pending,
        [$equipment],
    );
    $otherRequest = operationsRequest(
        $otherBorrower,
        'BR-POLICY-OTHER',
        BorrowRequestStatus::Pending,
        [],
    );

    expect($borrower->can('view', $ownRequest))->toBeTrue()
        ->and($borrower->can('view', $otherRequest))->toBeFalse()
        ->and($borrower->can('approve', $otherRequest))->toBeFalse()
        ->and($borrower->can('checkout', $otherRequest))->toBeFalse()
        ->and($borrower->can('receiveReturn', $otherRequest))->toBeFalse();

    $this->actingAs($borrower)
        ->patch(route('approval.update', $otherRequest), [
            'action' => ApprovalAction::Approved->value,
        ])
        ->assertForbidden();

    $this->actingAs($borrower)
        ->patch(route('checkout.update', $otherRequest), [
            'action' => ProcessEquipmentCheckoutRequest::ACTION_READY,
        ])
        ->assertForbidden();

    $this->actingAs($borrower)
        ->patch(route('return.update', $otherRequest), ['items' => []])
        ->assertForbidden();

    $this->actingAs($borrower)
        ->get(route('admin.users.edit', $otherBorrower))
        ->assertForbidden();

    expect($otherRequest->refresh()->status)->toBe(BorrowRequestStatus::Pending);
});

it('validates list and report filters before building queries', function () {
    $superAdmin = operationsUser('SuperAdmin');

    $this->actingAs($superAdmin)
        ->get(route('equipment.index', ['status' => 'NOT_A_STATUS']))
        ->assertSessionHasErrors('status');

    $this->actingAs($superAdmin)
        ->get(route('report.borrowing', [
            'from' => today()->format('Y-m-d'),
            'to' => today()->subDay()->format('Y-m-d'),
        ]))
        ->assertSessionHasErrors('to');

    $this->actingAs($superAdmin)
        ->get(route('admin.users', ['active' => 'invalid']))
        ->assertSessionHasErrors('active');

    $this->actingAs($superAdmin)
        ->get(route('category.index', ['active' => 'invalid']))
        ->assertSessionHasErrors('active');

    $this->actingAs($superAdmin)
        ->get(route('maintenance.index', ['state' => 'NOT_A_STATE']))
        ->assertSessionHasErrors('state');
});

it('prepares and checks out every item in an approved request', function () {
    $borrower = User::factory()->create();
    $staff = operationsUser('Staff');
    $first = operationsEquipment('CHECKOUT-001', EquipmentStatus::Reserved);
    $second = operationsEquipment('CHECKOUT-002', EquipmentStatus::Reserved);
    $borrowRequest = operationsRequest(
        $borrower,
        'BR-CHECKOUT-001',
        BorrowRequestStatus::Approved,
        [$first, $second],
    );

    $this->actingAs($staff)
        ->patch(route('checkout.update', $borrowRequest), [
            'action' => ProcessEquipmentCheckoutRequest::ACTION_READY,
        ])
        ->assertRedirect(route('checkout.index'));

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::ReadyForPickup);

    $conditions = $borrowRequest->items()
        ->pluck('id')
        ->mapWithKeys(fn (int $id): array => [$id => 'สภาพปกติ'])
        ->all();

    $this->actingAs($staff)
        ->patch(route('checkout.update', $borrowRequest), [
            'action' => ProcessEquipmentCheckoutRequest::ACTION_CHECKOUT,
            'conditions' => $conditions,
            'note' => 'Handed to borrower',
        ])
        ->assertRedirect(route('checkout.index'));

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Borrowed)
        ->and($borrowRequest->items()->where('status', BorrowItemStatus::Borrowed->value)->count())->toBe(2)
        ->and(Equipment::query()->whereKey([$first->id, $second->id])->where('status', EquipmentStatus::Borrowed->value)->count())->toBe(2);

    $this->assertDatabaseCount('equipment_checkouts', 2);
    $this->assertDatabaseHas('audit_logs', ['event' => 'checkout.processed']);
});

it('receives returned equipment and maps every outcome to equipment and incident statuses', function () {
    $borrower = User::factory()->create();
    $staff = operationsUser('Staff');
    $equipment = collect(ReturnStatus::cases())->mapWithKeys(fn (ReturnStatus $status): array => [
        $status->value => operationsEquipment('RETURN-'.$status->value, EquipmentStatus::Borrowed),
    ]);
    $borrowRequest = operationsRequest(
        $borrower,
        'BR-RETURN-001',
        BorrowRequestStatus::Borrowed,
        $equipment->values()->all(),
    );
    $items = $borrowRequest->items()->get()->keyBy('equipment_id');
    $payload = [];

    foreach ($equipment as $statusValue => $itemEquipment) {
        $item = $items->get($itemEquipment->id);
        $payload[$item->id] = [
            'return_status' => $statusValue,
            'condition_after' => 'Return result '.$statusValue,
            'note' => null,
        ];
    }

    $this->actingAs($staff)
        ->patch(route('return.update', $borrowRequest), ['items' => $payload])
        ->assertRedirect(route('return.index'));

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Returned);

    foreach ($equipment as $statusValue => $itemEquipment) {
        expect($itemEquipment->refresh()->status)->toBe(ReturnStatus::from($statusValue)->equipmentStatus());
    }

    $this->assertDatabaseCount('equipment_returns', count(ReturnStatus::cases()));
    $this->assertDatabaseCount('equipment_incidents', 3);
    $this->assertDatabaseHas('audit_logs', ['event' => 'return.processed']);
});

it('manages user roles, custom roles, permissions and system settings', function () {
    $superAdmin = operationsUser('SuperAdmin');
    $managedUser = operationsUser('Borrower');
    $adminRole = Role::findByName('Admin');

    $this->actingAs($superAdmin)
        ->patch(route('admin.users.update', $managedUser), [
            'active' => '1',
            'roles' => [$adminRole->id],
        ])
        ->assertRedirect(route('admin.users'));
    expect($managedUser->refresh()->hasRole('Admin'))->toBeTrue();

    $permission = Permission::findByName('equipment.view');
    $this->actingAs($superAdmin)
        ->post(route('admin.roles.store'), [
            'name' => 'Auditor Team',
            'permissions' => [$permission->id],
        ])
        ->assertRedirect(route('admin.roles'));
    $customRole = Role::findByName('Auditor Team');
    expect($customRole->hasPermissionTo('equipment.view'))->toBeTrue();

    $this->actingAs($superAdmin)
        ->delete(route('admin.roles.destroy', $customRole))
        ->assertRedirect(route('admin.roles'));
    expect(Role::query()->where('name', 'Auditor Team')->exists())->toBeFalse();

    $this->actingAs($superAdmin)
        ->patch(route('admin.settings.update'), [
            'organization_name' => 'Test Organization',
            'default_loan_days' => 5,
            'max_items_per_request' => 2,
            'overdue_alert_days' => 1,
            'contact_email' => 'help@example.test',
            'allow_weekend_borrow' => '0',
        ])
        ->assertRedirect(route('admin.settings'));

    expect(SystemSetting::read('organization_name'))->toBe('Test Organization')
        ->and(SystemSetting::read('max_items_per_request'))->toBe(2)
        ->and(SystemSetting::read('allow_weekend_borrow'))->toBeFalse();

    $this->assertDatabaseHas('audit_logs', ['event' => 'user.updated']);
    $this->assertDatabaseHas('audit_logs', ['event' => 'settings.updated']);
});

it('keeps at least one active super administrator under user management', function () {
    $superAdmin = operationsUser('SuperAdmin');
    $manager = User::factory()->create();
    $manager->givePermissionTo('user.manage');
    $superAdminRole = Role::findByName('SuperAdmin');

    $this->actingAs($manager)
        ->patch(route('admin.users.update', $superAdmin), [
            'active' => '0',
            'roles' => [$superAdminRole->id],
        ])
        ->assertSessionHasErrors('active');

    expect($superAdmin->refresh()->active)->toBeTrue()
        ->and($superAdmin->hasRole('SuperAdmin'))->toBeTrue();
});

it('enforces the configured maximum equipment count per borrow request', function () {
    $borrower = operationsUser('Borrower');
    SystemSetting::query()->where('key', 'max_items_per_request')->update(['value' => '2']);
    $equipment = [
        operationsEquipment('LIMIT-001', EquipmentStatus::Available),
        operationsEquipment('LIMIT-002', EquipmentStatus::Available),
        operationsEquipment('LIMIT-003', EquipmentStatus::Available),
    ];

    $this->actingAs($borrower)
        ->post(route('borrow.store'), [
            'purpose' => 'Too many items',
            'borrow_date' => today()->format('Y-m-d'),
            'expected_return_date' => today()->addDay()->format('Y-m-d'),
            'equipment_ids' => collect($equipment)->pluck('id')->all(),
            'accept_terms' => '1',
        ])
        ->assertSessionHasErrors('equipment_ids');

    expect(BorrowRequest::query()->count())->toBe(0);
});

it('enforces the weekend policy and marks past borrowed requests overdue', function () {
    $borrower = operationsUser('Borrower');
    SystemSetting::query()->where('key', 'allow_weekend_borrow')->update(['value' => '0']);
    $equipment = operationsEquipment('WEEKEND-001', EquipmentStatus::Available);
    $saturday = today()->next(Carbon::SATURDAY);

    $this->actingAs($borrower)
        ->post(route('borrow.store'), [
            'purpose' => 'Weekend request',
            'borrow_date' => $saturday->format('Y-m-d'),
            'expected_return_date' => $saturday->copy()->addDay()->format('Y-m-d'),
            'equipment_ids' => [$equipment->id],
            'accept_terms' => '1',
        ])
        ->assertSessionHasErrors('borrow_date');

    $borrowRequest = operationsRequest(
        $borrower,
        'BR-PAST-DUE',
        BorrowRequestStatus::Borrowed,
        [operationsEquipment('OVERDUE-001', EquipmentStatus::Borrowed)],
    );
    $borrowRequest->update(['expected_return_date' => today()->subDay()]);

    $this->artisan('borrow:mark-overdue')
        ->expectsOutput('Marked 1 borrow request(s) as overdue.')
        ->assertSuccessful();

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Overdue);
});
