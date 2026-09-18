<?php

use App\Enums\ApprovalAction;
use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Enums\IncidentType;
use App\Enums\ReturnStatus;
use App\Models\AuditLog;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\BorrowRequestInternalNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function experienceEquipment(string $code): Equipment
{
    $category = EquipmentCategory::query()->firstOrCreate(
        ['code' => 'EXP'],
        ['name' => 'Experience equipment', 'active' => true],
    );

    return Equipment::query()->create([
        'category_id' => $category->id,
        'equipment_code' => $code,
        'name' => 'Equipment '.$code,
        'status' => EquipmentStatus::Available,
        'active' => true,
    ]);
}

function experienceBorrowRequest(
    User $borrower,
    Equipment $equipment,
    string $number,
    BorrowRequestStatus $status = BorrowRequestStatus::Pending,
): BorrowRequest {
    $borrowRequest = BorrowRequest::query()->create([
        'request_no' => $number,
        'user_id' => $borrower->id,
        'purpose' => 'ใช้สำหรับทดสอบหน้ารายละเอียด',
        'usage_location' => 'ห้องประชุม',
        'borrow_date' => today(),
        'expected_return_date' => today()->addDays(2),
        'status' => $status,
        'submitted_at' => $status === BorrowRequestStatus::Draft ? null : now(),
    ]);

    $borrowRequest->items()->create([
        'equipment_id' => $equipment->id,
        'status' => match ($status) {
            BorrowRequestStatus::Draft => BorrowItemStatus::Draft,
            BorrowRequestStatus::Returned => BorrowItemStatus::Returned,
            BorrowRequestStatus::Borrowed, BorrowRequestStatus::Overdue => BorrowItemStatus::Borrowed,
            default => BorrowItemStatus::Pending,
        },
    ]);

    return $borrowRequest;
}

it('shows equipment approvals checkout return incidents timeline and audit on one authorized detail page', function () {
    $borrower = User::factory()->create(['name' => 'Detail Borrower']);
    $borrower->assignRole('Borrower');
    $otherBorrower = User::factory()->create();
    $otherBorrower->assignRole('Borrower');
    $staff = User::factory()->create(['name' => 'Detail Staff']);
    $staff->assignRole('Staff');
    $approver = User::factory()->create(['name' => 'Detail Approver']);
    $equipment = experienceEquipment('EQ-DETAIL-TIMELINE');
    $borrowRequest = experienceBorrowRequest(
        $borrower,
        $equipment,
        'BR-DETAIL-001',
        BorrowRequestStatus::Returned,
    );
    $item = $borrowRequest->items()->sole();

    $borrowRequest->approvals()->create([
        'approver_id' => $approver->id,
        'action' => ApprovalAction::Approved,
        'comment' => 'อนุมัติสำหรับการประชุม',
        'acted_at' => now()->subHours(3),
    ]);
    $item->checkout()->create([
        'checked_out_by' => $staff->id,
        'checked_out_at' => now()->subHours(2),
        'condition_before' => 'สภาพพร้อมใช้งาน',
    ]);
    $item->equipmentReturn()->create([
        'received_by' => $staff->id,
        'returned_at' => now()->subHour(),
        'condition_after' => 'คืนครบและสภาพปกติ',
        'return_status' => ReturnStatus::Normal,
    ]);
    $borrowRequest->incidents()->create([
        'equipment_id' => $equipment->id,
        'type' => IncidentType::Other,
        'description' => 'พบสายอุปกรณ์หลวม',
        'reported_by' => $staff->id,
        'reported_at' => now()->subMinutes(30),
    ]);
    AuditLog::query()->create([
        'causer_id' => $staff->id,
        'event' => 'return.processed',
        'subject_type' => $borrowRequest->getMorphClass(),
        'subject_id' => $borrowRequest->id,
        'new_values' => ['status' => BorrowRequestStatus::Returned->value],
    ]);

    $this->actingAs($borrower)
        ->get(route('borrow.show', $borrowRequest))
        ->assertOk()
        ->assertSee('BR-DETAIL-001')
        ->assertSee('EQ-DETAIL-TIMELINE')
        ->assertSee('อนุมัติสำหรับการประชุม')
        ->assertSee('สภาพพร้อมใช้งาน')
        ->assertSee('คืนครบและสภาพปกติ')
        ->assertSee('พบสายอุปกรณ์หลวม')
        ->assertSee('return.processed');

    $this->actingAs($otherBorrower)
        ->get(route('borrow.show', $borrowRequest))
        ->assertForbidden();

    $this->actingAs($staff)
        ->get(route('borrow.show', $borrowRequest))
        ->assertOk();
});

it('saves a draft then lets only its owner edit and submit it', function () {
    Notification::fake();

    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $otherBorrower = User::factory()->create();
    $otherBorrower->assignRole('Borrower');
    $approver = User::factory()->create();
    $approver->assignRole('Approver');
    $firstEquipment = experienceEquipment('EQ-DRAFT-001');
    $secondEquipment = experienceEquipment('EQ-DRAFT-002');

    $this->actingAs($borrower)
        ->post(route('borrow.store'), [
            'intent' => 'draft',
            'purpose' => 'ฉบับร่างสำหรับงานประชุม',
            'borrow_date' => today()->addDay()->format('Y-m-d'),
            'expected_return_date' => today()->addDays(2)->format('Y-m-d'),
            'equipment_ids' => [$firstEquipment->id],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $borrowRequest = BorrowRequest::query()->sole();

    expect($borrowRequest->status)->toBe(BorrowRequestStatus::Draft)
        ->and($borrowRequest->submitted_at)->toBeNull()
        ->and($borrowRequest->terms_accepted_at)->toBeNull()
        ->and($borrowRequest->items()->sole()->status)->toBe(BorrowItemStatus::Draft);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'borrow.draft.created',
        'subject_id' => $borrowRequest->id,
    ]);
    Notification::assertNotSentTo($approver, BorrowRequestInternalNotification::class);

    $this->actingAs($otherBorrower)
        ->get(route('borrow.edit', $borrowRequest))
        ->assertForbidden();

    $this->actingAs($borrower)
        ->get(route('borrow.edit', $borrowRequest))
        ->assertOk()
        ->assertSee('ฉบับร่างสำหรับงานประชุม')
        ->assertSee('value="'.$firstEquipment->id.'"', false);

    $this->patch(route('borrow.update', $borrowRequest), [
        'intent' => 'submit',
        'purpose' => 'ส่งคำขอสำหรับงานประชุม',
        'borrow_date' => today()->addDay()->format('Y-m-d'),
        'expected_return_date' => today()->addDays(3)->format('Y-m-d'),
        'equipment_ids' => [$secondEquipment->id],
        'accept_terms' => '1',
    ])->assertRedirect(route('borrow.mine'));

    $borrowRequest->refresh();

    expect($borrowRequest->status)->toBe(BorrowRequestStatus::Pending)
        ->and($borrowRequest->submitted_at)->not->toBeNull()
        ->and($borrowRequest->terms_accepted_at)->not->toBeNull()
        ->and($borrowRequest->items()->sole()->equipment_id)->toBe($secondEquipment->id)
        ->and($borrowRequest->items()->sole()->status)->toBe(BorrowItemStatus::Pending);

    Notification::assertSentTo($approver, BorrowRequestInternalNotification::class);
});

it('shows internal notifications in the bell and marks only an owned notification as read', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $equipment = experienceEquipment('EQ-NOTIFICATION-001');
    $borrowRequest = experienceBorrowRequest($borrower, $equipment, 'BR-NOTIFY-001');

    $borrower->notify(new BorrowRequestInternalNotification(
        event: 'borrow.approved',
        borrowRequestId: $borrowRequest->id,
        requestNo: $borrowRequest->request_no,
        title: 'คำขอได้รับการอนุมัติแล้ว',
        message: 'เปิดเพื่อดูรายละเอียดคำขอ',
    ));
    $notification = $borrower->notifications()->sole();

    $this->actingAs($borrower)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('คำขอได้รับการอนุมัติแล้ว')
        ->assertSee('1 ใหม่');

    $this->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('borrow.show', $borrowRequest));

    expect($notification->fresh()->read_at)->not->toBeNull();

    $otherBorrower = User::factory()->create();
    $otherBorrower->assignRole('Borrower');
    $this->actingAs($otherBorrower)
        ->post(route('notifications.read', $notification->id))
        ->assertNotFound();
});

it('sends one due reminder and audits an automatic overdue transition', function () {
    $borrower = User::factory()->create();
    $equipment = experienceEquipment('EQ-DUE-001');
    $borrowRequest = experienceBorrowRequest(
        $borrower,
        $equipment,
        'BR-DUE-001',
        BorrowRequestStatus::Borrowed,
    );
    SystemSetting::query()->create([
        'key' => 'overdue_alert_days',
        'value' => '2',
        'type' => 'integer',
    ]);

    $this->artisan('borrow:send-due-reminders')
        ->expectsOutput('Sent 1 due reminder(s).')
        ->assertSuccessful();
    $this->artisan('borrow:send-due-reminders')
        ->expectsOutput('Sent 0 due reminder(s).')
        ->assertSuccessful();

    expect($borrower->notifications()->where('data->event', 'borrow.due_soon')->count())->toBe(1);

    $borrowRequest->update(['expected_return_date' => today()->subDay()]);
    $this->artisan('borrow:mark-overdue')
        ->expectsOutput('Marked 1 borrow request(s) as overdue.')
        ->assertSuccessful();

    expect($borrowRequest->refresh()->status)->toBe(BorrowRequestStatus::Overdue)
        ->and($borrower->notifications()->where('data->event', 'borrow.overdue')->count())->toBe(1);
    $this->assertDatabaseHas('audit_logs', [
        'event' => 'borrow.marked_overdue',
        'subject_id' => $borrowRequest->id,
        'causer_id' => null,
    ]);
});

it('renders a privacy-safe monthly availability calendar with category filtering', function () {
    $borrower = User::factory()->create();
    $borrower->assignRole('Borrower');
    $otherBorrower = User::factory()->create(['name' => 'Hidden Calendar Borrower']);
    $reservedEquipment = experienceEquipment('EQ-CALENDAR-001');
    experienceEquipment('EQ-CALENDAR-002');
    $borrowRequest = experienceBorrowRequest($otherBorrower, $reservedEquipment, 'BR-CALENDAR-001');
    $borrowRequest->update([
        'borrow_date' => today()->addDay(),
        'expected_return_date' => today()->addDay(),
    ]);

    $this->actingAs($borrower)
        ->get(route('borrow.calendar', [
            'month' => today()->format('Y-m'),
            'category_id' => $reservedEquipment->category_id,
        ]))
        ->assertOk()
        ->assertSee('ปฏิทินการจองและความพร้อมใช้งาน')
        ->assertSee('ว่าง 1/2')
        ->assertSee('มี 1 คำขอ')
        ->assertDontSee('Hidden Calendar Borrower');
});
