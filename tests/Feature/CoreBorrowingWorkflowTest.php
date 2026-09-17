<?php

use App\Enums\ApprovalAction;
use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function workflowEquipment(array $overrides = []): Equipment
{
    static $number = 0;
    $number++;

    $category = EquipmentCategory::query()->firstOrCreate(
        ['code' => 'TEST'],
        ['name' => 'Test equipment', 'active' => true],
    );

    return Equipment::query()->create(array_merge([
        'category_id' => $category->id,
        'equipment_code' => 'EQ-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
        'name' => 'Notebook '.$number,
        'status' => EquipmentStatus::Available,
        'active' => true,
    ], $overrides));
}

function workflowBorrowRequest(User $borrower, Equipment $equipment, string $number): BorrowRequest
{
    $borrowRequest = BorrowRequest::query()->create([
        'request_no' => $number,
        'user_id' => $borrower->id,
        'purpose' => 'Use for a project meeting',
        'borrow_date' => today(),
        'expected_return_date' => today()->addDay(),
        'status' => BorrowRequestStatus::Pending,
        'submitted_at' => now(),
    ]);

    $borrowRequest->items()->create([
        'equipment_id' => $equipment->id,
        'status' => BorrowItemStatus::Pending,
    ]);

    return $borrowRequest;
}

it('shows a searchable equipment catalogue to borrowers', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');

    workflowEquipment(['equipment_code' => 'EQ-MATCH', 'name' => 'Meeting Notebook']);
    workflowEquipment(['equipment_code' => 'EQ-HIDDEN', 'name' => 'Projector']);

    $this->actingAs($borrower)
        ->get(route('equipment.index', ['search' => 'Meeting']))
        ->assertOk()
        ->assertSee('EQ-MATCH')
        ->assertDontSee('EQ-HIDDEN');
});

it('allows equipment managers to register equipment', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = EquipmentCategory::query()->create([
        'code' => 'NOTEBOOK',
        'name' => 'Notebook',
        'active' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('equipment.create'))
        ->assertOk()
        ->assertSee('Notebook')
        ->assertSee('data-auto-code="equipment"', false)
        ->assertDontSee('name="equipment_code"', false);

    $this->actingAs($admin)
        ->post(route('equipment.store'), [
            'category_id' => $category->id,
            'asset_number' => 'ASSET-0001',
            'name' => 'Presentation notebook',
            'brand' => 'Example',
            'model' => 'Pro',
            'location' => 'Storage room A',
            'status' => EquipmentStatus::Available->value,
            'active' => '1',
        ])
        ->assertRedirect(route('equipment.index'));

    $equipment = Equipment::query()->where('asset_number', 'ASSET-0001')->firstOrFail();

    expect($equipment->equipment_code)->toMatch('/^EQ-\d{6}$/');

    $this->assertDatabaseHas('equipment', [
        'equipment_code' => $equipment->equipment_code,
        'name' => 'Presentation notebook',
        'status' => EquipmentStatus::Available->value,
        'active' => true,
    ]);
});

it('creates a pending borrow request with a monthly sequence number', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $equipment = workflowEquipment();

    $this->actingAs($borrower)
        ->get(route('borrow.create'))
        ->assertOk()
        ->assertSee($equipment->equipment_code)
        ->assertSee('name="accept_terms"', false)
        ->assertSee('data-borrow-submit', false);

    $payload = [
        'purpose' => 'Use for an off-site presentation',
        'usage_location' => 'Meeting room A',
        'borrow_date' => today()->format('Y-m-d'),
        'expected_return_date' => today()->addDays(2)->format('Y-m-d'),
        'equipment_ids' => [$equipment->id],
    ];

    $this->actingAs($borrower)
        ->post(route('borrow.store'), $payload)
        ->assertSessionHasErrors('accept_terms');

    expect(BorrowRequest::query()->count())->toBe(0);

    $response = $this->actingAs($borrower)->post(route('borrow.store'), [
        ...$payload,
        'accept_terms' => '1',
    ]);

    $response->assertRedirect(route('borrow.mine'));

    $borrowRequest = BorrowRequest::query()->sole();

    expect($borrowRequest->request_no)
        ->toMatch('/^BR-\d{6}-00001$/')
        ->and($borrowRequest->status)->toBe(BorrowRequestStatus::Pending)
        ->and($borrowRequest->submitted_at)->not->toBeNull()
        ->and($borrowRequest->terms_accepted_at)->not->toBeNull()
        ->and($borrowRequest->terms_version)->toBe(config('borrowing.terms.version'));

    $this->assertDatabaseHas('borrow_request_items', [
        'borrow_request_id' => $borrowRequest->id,
        'equipment_id' => $equipment->id,
        'status' => BorrowItemStatus::Pending->value,
    ]);
});

it('limits borrowers to their own requests while staff can see every request', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $otherBorrower = User::factory()->create();
    $staff = User::factory()->create();
    $staff->assignRole('Staff');

    workflowBorrowRequest($borrower, workflowEquipment(), 'BR-OWN-001');
    workflowBorrowRequest($otherBorrower, workflowEquipment(), 'BR-OTHER-001');

    $this->actingAs($borrower)
        ->get(route('borrow.mine'))
        ->assertOk()
        ->assertSee('BR-OWN-001')
        ->assertDontSee('BR-OTHER-001');

    $this->actingAs($staff)
        ->get(route('borrow.index'))
        ->assertOk()
        ->assertSee('BR-OWN-001')
        ->assertSee('BR-OTHER-001');
});

it('allows an approver to approve a pending request and reserves its equipment', function () {
    $borrower = User::factory()->create();
    $approver = User::factory()->create();
    $approver->assignRole('Approver');
    $equipment = workflowEquipment();
    $borrowRequest = workflowBorrowRequest($borrower, $equipment, 'BR-APPROVE-001');

    $this->actingAs($approver)
        ->get(route('approval.index'))
        ->assertOk()
        ->assertSee('BR-APPROVE-001')
        ->assertSee($equipment->equipment_code);

    $this->actingAs($approver)
        ->patch(route('approval.update', $borrowRequest), [
            'action' => ApprovalAction::Approved->value,
            'comment' => 'Approved for the requested period',
        ])
        ->assertRedirect(route('approval.index'));

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Approved)
        ->and($borrowRequest->items()->first()->status)->toBe(BorrowItemStatus::Reserved)
        ->and($equipment->refresh()->status)->toBe(EquipmentStatus::Reserved);

    $this->assertDatabaseHas('borrow_approvals', [
        'borrow_request_id' => $borrowRequest->id,
        'approver_id' => $approver->id,
        'action' => ApprovalAction::Approved->value,
    ]);
});

it('requires a reason for rejection and leaves equipment available', function () {
    $borrower = User::factory()->create();
    $approver = User::factory()->create();
    $approver->assignRole('Approver');
    $equipment = workflowEquipment();
    $borrowRequest = workflowBorrowRequest($borrower, $equipment, 'BR-REJECT-001');

    $this->actingAs($approver)
        ->patch(route('approval.update', $borrowRequest), [
            'action' => ApprovalAction::Rejected->value,
        ])
        ->assertSessionHasErrors('comment');

    $this->actingAs($approver)
        ->patch(route('approval.update', $borrowRequest), [
            'action' => ApprovalAction::Rejected->value,
            'comment' => 'The requested period is unavailable',
        ])
        ->assertRedirect(route('approval.index'));

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Rejected)
        ->and($borrowRequest->items()->first()->status)->toBe(BorrowItemStatus::Rejected)
        ->and($equipment->refresh()->status)->toBe(EquipmentStatus::Available);
});
