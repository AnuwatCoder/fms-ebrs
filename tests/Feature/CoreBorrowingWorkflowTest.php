<?php

use App\Enums\ApprovalAction;
use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\BorrowRequestApprovedNotification;
use App\Notifications\NewBorrowRequestNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

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

it('shows equipment details and permission-aware actions from the catalogue', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $equipment = workflowEquipment([
        'equipment_code' => 'EQ-DETAIL',
        'asset_number' => 'ASSET-DETAIL',
        'name' => 'Notebook for detail view',
        'brand' => 'Example Brand',
        'model' => 'Model A',
        'serial_number' => 'SERIAL-DETAIL',
        'location' => 'Storage room B',
        'purchase_date' => today()->subYear(),
        'description' => 'Equipment detail description',
    ]);

    $this->actingAs($borrower)
        ->get(route('equipment.index'))
        ->assertOk()
        ->assertSee('href="'.route('equipment.show', $equipment).'"', false)
        ->assertDontSee('href="'.route('equipment.edit', $equipment).'"', false)
        ->assertDontSee('action="'.route('equipment.destroy', $equipment).'"', false);

    $this->get(route('equipment.show', $equipment))
        ->assertOk()
        ->assertSee('Notebook for detail view')
        ->assertSee('ASSET-DETAIL')
        ->assertSee('Example Brand')
        ->assertSee('SERIAL-DETAIL')
        ->assertDontSee('href="'.route('equipment.edit', $equipment).'"', false);

    $this->get(route('equipment.edit', $equipment))->assertForbidden();
    $this->delete(route('equipment.destroy', $equipment))->assertForbidden();

    $this->actingAs($admin)
        ->get(route('equipment.index'))
        ->assertOk()
        ->assertSee('href="'.route('equipment.edit', $equipment).'"', false)
        ->assertSee('action="'.route('equipment.destroy', $equipment).'"', false);

    $this->get('/equipment/'.$equipment->getKey())->assertNotFound();
});

it('allows equipment managers to edit equipment without changing workflow status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $equipment = workflowEquipment([
        'equipment_code' => 'EQ-EDIT',
        'asset_number' => 'ASSET-OLD',
        'name' => 'Old equipment name',
        'status' => EquipmentStatus::Maintenance,
    ]);
    $category = EquipmentCategory::query()->create([
        'code' => 'EDIT-TARGET',
        'name' => 'Edit target category',
        'active' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('equipment.edit', $equipment))
        ->assertOk()
        ->assertSee('Old equipment name')
        ->assertSee('สถานะจะเปลี่ยนตามขั้นตอนการยืม คืน และซ่อมบำรุง')
        ->assertDontSee('name="status"', false);

    $this->patch(route('equipment.update', $equipment), [
        'category_id' => $category->id,
        'asset_number' => 'ASSET-NEW',
        'name' => 'Updated equipment name',
        'description' => 'Updated description',
        'brand' => 'Updated brand',
        'model' => 'Updated model',
        'serial_number' => 'SERIAL-UPDATED',
        'location' => 'Storage room C',
        'purchase_date' => today()->subMonth()->format('Y-m-d'),
        'active' => '0',
        'status' => EquipmentStatus::Available->value,
    ])->assertRedirect(route('equipment.show', $equipment));

    $equipment->refresh();

    expect($equipment->name)->toBe('Updated equipment name')
        ->and($equipment->category_id)->toBe($category->id)
        ->and($equipment->asset_number)->toBe('ASSET-NEW')
        ->and($equipment->status)->toBe(EquipmentStatus::Maintenance)
        ->and($equipment->active)->toBeFalse();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'equipment.updated',
        'subject_id' => $equipment->id,
    ]);
});

it('preserves database-wide unique equipment identifiers during updates', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $equipment = workflowEquipment(['equipment_code' => 'EQ-UNIQUE-TARGET']);
    $softDeletedEquipment = workflowEquipment([
        'equipment_code' => 'EQ-UNIQUE-DELETED',
        'asset_number' => 'ASSET-RESERVED',
        'serial_number' => 'SERIAL-RESERVED',
    ]);
    $softDeletedEquipment->delete();

    $this->actingAs($admin)
        ->from(route('equipment.edit', $equipment))
        ->patch(route('equipment.update', $equipment), [
            'category_id' => $equipment->category_id,
            'asset_number' => 'ASSET-RESERVED',
            'name' => $equipment->name,
            'serial_number' => 'SERIAL-RESERVED',
            'active' => '1',
        ])
        ->assertRedirect(route('equipment.edit', $equipment))
        ->assertSessionHasErrors(['asset_number', 'serial_number']);
});

it('soft deletes unused equipment and protects equipment with workflow history', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $borrower = User::factory()->create();
    $unusedEquipment = workflowEquipment(['equipment_code' => 'EQ-DELETE']);
    $usedEquipment = workflowEquipment(['equipment_code' => 'EQ-PROTECTED']);
    workflowBorrowRequest($borrower, $usedEquipment, 'BR-EQUIPMENT-PROTECTED');

    $this->actingAs($admin)
        ->delete(route('equipment.destroy', $unusedEquipment))
        ->assertRedirect(route('equipment.index'));

    $this->assertSoftDeleted('equipment', ['id' => $unusedEquipment->id]);
    $this->assertDatabaseHas('audit_logs', [
        'event' => 'equipment.deleted',
        'subject_id' => $unusedEquipment->id,
    ]);

    $this->from(route('equipment.show', $usedEquipment))
        ->delete(route('equipment.destroy', $usedEquipment))
        ->assertRedirect(route('equipment.show', $usedEquipment))
        ->assertSessionHasErrors('equipment');

    expect($usedEquipment->fresh())->not->toBeNull();
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
        ->assertSee('data-borrow-submit', false)
        ->assertSee('data-availability-feedback', false)
        ->assertSee('borrow-requests\/availability', false);

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

it('returns only equipment available throughout the selected dates', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $otherBorrower = User::factory()->create();
    $availableEquipment = workflowEquipment([
        'equipment_code' => 'EQ-DATE-AVAILABLE',
        'name' => 'Available notebook',
    ]);
    $overlappingEquipment = workflowEquipment([
        'equipment_code' => 'EQ-DATE-OVERLAP',
        'name' => 'Overlapping notebook',
    ]);
    $availableAfterEarlierBooking = workflowEquipment([
        'equipment_code' => 'EQ-DATE-LATER',
        'name' => 'Notebook available after earlier booking',
    ]);
    workflowEquipment([
        'equipment_code' => 'EQ-DATE-DAMAGED',
        'name' => 'Damaged notebook',
        'status' => EquipmentStatus::Damaged,
    ]);

    $requestedStart = today()->addDays(10);
    $requestedEnd = today()->addDays(12);

    $overlappingRequest = workflowBorrowRequest(
        $otherBorrower,
        $overlappingEquipment,
        'BR-DATE-OVERLAP',
    );
    $overlappingRequest->update([
        'borrow_date' => $requestedStart->copy()->subDay(),
        'expected_return_date' => $requestedStart,
    ]);

    $earlierRequest = workflowBorrowRequest(
        $otherBorrower,
        $availableAfterEarlierBooking,
        'BR-DATE-EARLIER',
    );
    $earlierRequest->update([
        'borrow_date' => $requestedStart->copy()->subDays(4),
        'expected_return_date' => $requestedStart->copy()->subDay(),
        'status' => BorrowRequestStatus::Approved,
    ]);
    $availableAfterEarlierBooking->update(['status' => EquipmentStatus::Reserved]);

    $this->actingAs($borrower)
        ->getJson(route('borrow.availability', [
            'borrow_date' => $requestedStart->format('Y-m-d'),
            'expected_return_date' => $requestedEnd->format('Y-m-d'),
        ]))
        ->assertOk()
        ->assertJsonPath('meta.count', 2)
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['equipment_code' => $availableEquipment->equipment_code])
        ->assertJsonFragment(['equipment_code' => $availableAfterEarlierBooking->equipment_code])
        ->assertJsonMissing(['equipment_code' => $overlappingEquipment->equipment_code])
        ->assertJsonMissing(['equipment_code' => 'EQ-DATE-DAMAGED']);

    $availableEquipment->update(['active' => false]);
    $availableAfterEarlierBooking->update(['active' => false]);

    $this->getJson(route('borrow.availability', [
        'borrow_date' => $requestedStart->format('Y-m-d'),
        'expected_return_date' => $requestedEnd->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertJsonPath('meta.count', 0)
        ->assertJsonPath(
            'meta.message',
            'ไม่พบอุปกรณ์ว่างในช่วงวันที่เลือก กรุณาเปลี่ยนช่วงวันที่หรือติดต่อเจ้าหน้าที่',
        )
        ->assertJsonCount(0, 'data');
});

it('protects the availability endpoint with borrow permission and date validation', function () {
    $approver = User::factory()->create();
    $approver->assignRole('Approver');

    $this->actingAs($approver)
        ->getJson(route('borrow.availability', [
            'borrow_date' => today()->format('Y-m-d'),
            'expected_return_date' => today()->addDay()->format('Y-m-d'),
        ]))
        ->assertForbidden();

    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');

    $this->actingAs($borrower)
        ->getJson(route('borrow.availability', [
            'borrow_date' => today()->addDays(2)->format('Y-m-d'),
            'expected_return_date' => today()->addDay()->format('Y-m-d'),
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('expected_return_date');
});

it('rejects conflicting equipment when a borrow request is submitted', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $otherBorrower = User::factory()->create();
    $equipment = workflowEquipment(['equipment_code' => 'EQ-SUBMIT-CONFLICT']);
    $existingRequest = workflowBorrowRequest($otherBorrower, $equipment, 'BR-EXISTING-CONFLICT');
    $existingRequest->update([
        'borrow_date' => today()->addDays(2),
        'expected_return_date' => today()->addDays(4),
    ]);

    $this->actingAs($borrower)
        ->post(route('borrow.store'), [
            'purpose' => 'Conflicting reservation',
            'borrow_date' => today()->addDays(3)->format('Y-m-d'),
            'expected_return_date' => today()->addDays(5)->format('Y-m-d'),
            'equipment_ids' => [$equipment->id],
            'accept_terms' => '1',
        ])
        ->assertSessionHasErrors('equipment_ids');

    expect(BorrowRequest::query()->count())->toBe(1);

    $this->actingAs($borrower)
        ->post(route('borrow.store'), [
            'purpose' => 'Non-conflicting reservation',
            'borrow_date' => today()->addDays(5)->format('Y-m-d'),
            'expected_return_date' => today()->addDays(6)->format('Y-m-d'),
            'equipment_ids' => [$equipment->id],
            'accept_terms' => '1',
        ])
        ->assertRedirect(route('borrow.mine'));

    expect(BorrowRequest::query()->count())->toBe(2);
});

it('emails active super administrators and administrators when a borrow request is submitted', function () {
    Notification::fake();

    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $inactiveAdmin = User::factory()->create(['active' => false]);
    $inactiveAdmin->assignRole('Admin');
    $adminWithoutEmail = User::factory()->create(['email' => null]);
    $adminWithoutEmail->assignRole('Admin');
    $approver = User::factory()->create();
    $approver->assignRole('Approver');
    $equipment = workflowEquipment(['equipment_code' => 'EQ-NOTIFY-NEW']);
    $payload = [
        'purpose' => 'Notification test',
        'borrow_date' => today()->format('Y-m-d'),
        'expected_return_date' => today()->addDay()->format('Y-m-d'),
        'equipment_ids' => [$equipment->id],
    ];

    $this->actingAs($borrower)
        ->post(route('borrow.store'), $payload)
        ->assertSessionHasErrors('accept_terms');

    Notification::assertNothingSent();

    $this->actingAs($borrower)
        ->post(route('borrow.store'), [
            ...$payload,
            'accept_terms' => '1',
        ])
        ->assertRedirect(route('borrow.mine'));

    $borrowRequest = BorrowRequest::query()->sole();

    foreach ([$superAdmin, $admin] as $recipient) {
        Notification::assertSentTo(
            $recipient,
            NewBorrowRequestNotification::class,
            function (NewBorrowRequestNotification $notification, array $channels) use ($borrowRequest, $recipient): bool {
                $mail = $notification->toMail($recipient);

                return $notification->borrowRequest->is($borrowRequest)
                    && $notification->itemCount === 1
                    && $notification->afterCommit === true
                    && $channels === ['mail']
                    && $mail->subject === "[{$borrowRequest->request_no}] มีคำขอยืมอุปกรณ์ใหม่";
            },
        );
    }

    Notification::assertNotSentTo(
        [$borrower, $inactiveAdmin, $adminWithoutEmail, $approver],
        NewBorrowRequestNotification::class,
    );
});

it('does not send borrow request emails when email notifications are disabled', function () {
    Notification::fake();

    SystemSetting::query()->updateOrCreate(
        ['key' => 'email_notifications_enabled'],
        ['value' => '0', 'type' => 'boolean'],
    );

    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $approver = User::factory()->create();
    $approver->assignRole('Approver');
    $equipment = workflowEquipment(['equipment_code' => 'EQ-NOTIFY-DISABLED']);

    $this->actingAs($borrower)
        ->post(route('borrow.store'), [
            'purpose' => 'Disabled notification test',
            'borrow_date' => today()->format('Y-m-d'),
            'expected_return_date' => today()->addDay()->format('Y-m-d'),
            'equipment_ids' => [$equipment->id],
            'accept_terms' => '1',
        ])
        ->assertRedirect(route('borrow.mine'));

    $borrowRequest = BorrowRequest::query()->sole();

    $this->actingAs($approver)
        ->patch(route('approval.update', $borrowRequest), [
            'action' => ApprovalAction::Approved->value,
            'comment' => 'Approved without email',
        ])
        ->assertRedirect(route('approval.index'));

    Notification::assertNothingSent();
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

it('allows borrowers to cancel their own pending requests from the mine page', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $equipment = workflowEquipment(['equipment_code' => 'EQ-CANCEL-PENDING']);
    $borrowRequest = workflowBorrowRequest($borrower, $equipment, 'BR-CANCEL-PENDING');

    $this->actingAs($borrower)
        ->get(route('borrow.mine'))
        ->assertOk()
        ->assertSee('BR-CANCEL-PENDING')
        ->assertSee('action="'.route('borrow.cancel', $borrowRequest).'"', false)
        ->assertSee('ยืนยันการยกเลิกคำขอ');

    $this->patch(route('borrow.cancel', $borrowRequest))
        ->assertRedirect(route('borrow.mine'))
        ->assertSessionHas('success', 'ยกเลิกคำขอ BR-CANCEL-PENDING เรียบร้อยแล้ว');

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Cancelled)
        ->and($borrowRequest->items()->first()->status)->toBe(BorrowItemStatus::Cancelled)
        ->and($equipment->refresh()->status)->toBe(EquipmentStatus::Available);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'borrow.cancelled',
        'subject_id' => $borrowRequest->id,
        'causer_id' => $borrower->id,
    ]);
});

it('releases only equipment without another active reservation when cancelling an approved request', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $otherBorrower = User::factory()->create();
    $sharedEquipment = workflowEquipment(['equipment_code' => 'EQ-CANCEL-SHARED']);
    $releasedEquipment = workflowEquipment(['equipment_code' => 'EQ-CANCEL-RELEASED']);
    $borrowRequest = workflowBorrowRequest($borrower, $sharedEquipment, 'BR-CANCEL-APPROVED');
    $borrowRequest->items()->create([
        'equipment_id' => $releasedEquipment->id,
        'status' => BorrowItemStatus::Reserved,
    ]);
    $borrowRequest->update(['status' => BorrowRequestStatus::Approved]);
    $borrowRequest->items()->update(['status' => BorrowItemStatus::Reserved->value]);
    $sharedEquipment->update(['status' => EquipmentStatus::Reserved]);
    $releasedEquipment->update(['status' => EquipmentStatus::Reserved]);

    $otherRequest = workflowBorrowRequest($otherBorrower, $sharedEquipment, 'BR-CANCEL-REMAINING');
    $otherRequest->update([
        'borrow_date' => today()->addDays(3),
        'expected_return_date' => today()->addDays(4),
        'status' => BorrowRequestStatus::Approved,
    ]);
    $otherRequest->items()->update(['status' => BorrowItemStatus::Reserved->value]);

    $this->actingAs($borrower)
        ->patch(route('borrow.cancel', $borrowRequest))
        ->assertRedirect(route('borrow.mine'));

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Cancelled)
        ->and($borrowRequest->items()->get()->pluck('status')->unique()->all())
        ->toBe([BorrowItemStatus::Cancelled])
        ->and($sharedEquipment->refresh()->status)->toBe(EquipmentStatus::Reserved)
        ->and($releasedEquipment->refresh()->status)->toBe(EquipmentStatus::Available)
        ->and($otherRequest->refresh()->status)->toBe(BorrowRequestStatus::Approved);
});

it('prevents cancelling another borrowers request or a request already borrowed', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $otherBorrower = User::factory()->create();
    $otherBorrower->assignRole('Borrower');
    $equipment = workflowEquipment(['equipment_code' => 'EQ-CANCEL-PROTECTED']);
    $borrowRequest = workflowBorrowRequest($borrower, $equipment, 'BR-CANCEL-PROTECTED');

    $this->actingAs($otherBorrower)
        ->patch(route('borrow.cancel', $borrowRequest))
        ->assertForbidden();

    $borrowRequest->update(['status' => BorrowRequestStatus::Borrowed]);
    $borrowRequest->items()->update(['status' => BorrowItemStatus::Borrowed->value]);
    $equipment->update(['status' => EquipmentStatus::Borrowed]);

    $this->actingAs($borrower)
        ->from(route('borrow.mine'))
        ->patch(route('borrow.cancel', $borrowRequest))
        ->assertRedirect(route('borrow.mine'))
        ->assertSessionHasErrors('cancel');

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Borrowed)
        ->and($borrowRequest->items()->first()->status)->toBe(BorrowItemStatus::Borrowed)
        ->and($equipment->refresh()->status)->toBe(EquipmentStatus::Borrowed);
});

it('allows an approver to approve a pending request and reserves its equipment', function () {
    Notification::fake();

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

    Notification::assertSentTo(
        $borrower,
        BorrowRequestApprovedNotification::class,
        function (BorrowRequestApprovedNotification $notification, array $channels) use ($borrowRequest, $borrower): bool {
            $mail = $notification->toMail($borrower);

            return $notification->borrowRequest->is($borrowRequest)
                && $notification->itemCount === 1
                && $notification->afterCommit === true
                && $channels === ['mail']
                && $mail->subject === '[BR-APPROVE-001] คำขอยืมอุปกรณ์ได้รับการอนุมัติแล้ว';
        },
    );
});

it('allows non-overlapping requests for reserved equipment to be approved', function () {
    Notification::fake();

    $borrower = User::factory()->create();
    $approver = User::factory()->create();
    $approver->assignRole('Approver');
    $equipment = workflowEquipment(['equipment_code' => 'EQ-APPROVE-NON-OVERLAP']);
    $firstRequest = workflowBorrowRequest($borrower, $equipment, 'BR-NON-OVERLAP-001');
    $firstRequest->update([
        'borrow_date' => today()->addDay(),
        'expected_return_date' => today()->addDays(2),
    ]);
    $secondRequest = workflowBorrowRequest($borrower, $equipment, 'BR-NON-OVERLAP-002');
    $secondRequest->update([
        'borrow_date' => today()->addDays(3),
        'expected_return_date' => today()->addDays(4),
    ]);

    $this->actingAs($approver)
        ->patch(route('approval.update', $firstRequest), [
            'action' => ApprovalAction::Approved->value,
        ])
        ->assertRedirect(route('approval.index'));

    $this->actingAs($approver)
        ->patch(route('approval.update', $secondRequest), [
            'action' => ApprovalAction::Approved->value,
        ])
        ->assertRedirect(route('approval.index'))
        ->assertSessionHasNoErrors();

    expect($firstRequest->refresh()->status)->toBe(BorrowRequestStatus::Approved)
        ->and($secondRequest->refresh()->status)->toBe(BorrowRequestStatus::Approved)
        ->and($equipment->refresh()->status)->toBe(EquipmentStatus::Reserved);
});

it('rejects approval when another approved request overlaps the selected dates', function () {
    Notification::fake();

    $borrower = User::factory()->create();
    $approver = User::factory()->create();
    $approver->assignRole('Approver');
    $equipment = workflowEquipment(['equipment_code' => 'EQ-APPROVE-OVERLAP']);
    $firstRequest = workflowBorrowRequest($borrower, $equipment, 'BR-OVERLAP-001');
    $firstRequest->update([
        'borrow_date' => today()->addDay(),
        'expected_return_date' => today()->addDays(3),
    ]);
    $secondRequest = workflowBorrowRequest($borrower, $equipment, 'BR-OVERLAP-002');
    $secondRequest->update([
        'borrow_date' => today()->addDays(3),
        'expected_return_date' => today()->addDays(4),
    ]);

    $this->actingAs($approver)
        ->patch(route('approval.update', $firstRequest), [
            'action' => ApprovalAction::Approved->value,
        ])
        ->assertRedirect(route('approval.index'));

    $this->actingAs($approver)
        ->patch(route('approval.update', $secondRequest), [
            'action' => ApprovalAction::Approved->value,
        ])
        ->assertSessionHasErrors('action');

    expect($firstRequest->refresh()->status)->toBe(BorrowRequestStatus::Approved)
        ->and($secondRequest->refresh()->status)->toBe(BorrowRequestStatus::Pending);
});

it('requires a reason for rejection and leaves equipment available', function () {
    Notification::fake();

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

    Notification::assertNotSentTo($borrower, BorrowRequestApprovedNotification::class);
});
