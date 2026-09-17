<?php

namespace Database\Seeders;

use App\Enums\ApprovalAction;
use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Enums\IncidentType;
use App\Enums\ReturnStatus;
use App\Models\AuditLog;
use App\Models\BorrowApproval;
use App\Models\BorrowRequest;
use App\Models\BorrowRequestItem;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentCheckout;
use App\Models\EquipmentIncident;
use App\Models\EquipmentReturn;
use App\Models\User;
use Illuminate\Database\Seeder;

class WorkflowStatusSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->users();
        $category = EquipmentCategory::query()->where('code', 'OTHER')->firstOrFail();

        $this->seedEquipmentStatuses($category);
        $requests = $this->seedBorrowStatuses($users['borrower'], $category);
        $this->seedApprovalActions($requests, $users['approver']);
        $this->seedCheckoutHistory($requests, $users['staff']);
        $this->seedReturnStatuses($requests[BorrowRequestStatus::Returned->value], $category, $users['staff']);
        $this->seedIncidents($requests, $category, $users['staff']);
        $this->seedAuditSamples($requests, $users);
    }

    /** @return array{borrower: User, approver: User, staff: User, admin: User} */
    private function users(): array
    {
        $users = [
            'borrower' => ['Demo Borrower', 'demo.borrower@example.test', 'demo.borrower', 'Borrower'],
            'approver' => ['Demo Approver', 'demo.approver@example.test', 'demo.approver', 'Approver'],
            'staff' => ['Demo Staff', 'demo.staff@example.test', 'demo.staff', 'Staff'],
            'admin' => ['Demo Administrator', 'demo.admin@example.test', 'demo.admin', 'Admin'],
        ];

        return collect($users)->mapWithKeys(function (array $attributes, string $key): array {
            [$name, $email, $username, $role] = $attributes;
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'active' => true,
                    'password' => null,
                ],
            );
            $user->syncRoles([$role]);

            return [$key => $user];
        })->all();
    }

    private function seedEquipmentStatuses(EquipmentCategory $category): void
    {
        foreach (EquipmentStatus::cases() as $status) {
            $code = 'DEMO-EQ-'.$status->value;
            $this->equipment($category, $code, 'Demo '.$status->label(), $status);
        }
    }

    /** @return array<string, BorrowRequest> */
    private function seedBorrowStatuses(User $borrower, EquipmentCategory $category): array
    {
        $itemStatuses = [
            BorrowRequestStatus::Draft->value => BorrowItemStatus::Draft,
            BorrowRequestStatus::Pending->value => BorrowItemStatus::Pending,
            BorrowRequestStatus::Approved->value => BorrowItemStatus::Reserved,
            BorrowRequestStatus::ReadyForPickup->value => BorrowItemStatus::ReadyForPickup,
            BorrowRequestStatus::Borrowed->value => BorrowItemStatus::Borrowed,
            BorrowRequestStatus::Returned->value => BorrowItemStatus::Returned,
            BorrowRequestStatus::Rejected->value => BorrowItemStatus::Rejected,
            BorrowRequestStatus::Cancelled->value => BorrowItemStatus::Cancelled,
            BorrowRequestStatus::Overdue->value => BorrowItemStatus::Borrowed,
        ];
        $equipmentStatuses = [
            BorrowRequestStatus::Approved->value => EquipmentStatus::Reserved,
            BorrowRequestStatus::ReadyForPickup->value => EquipmentStatus::Reserved,
            BorrowRequestStatus::Borrowed->value => EquipmentStatus::Borrowed,
            BorrowRequestStatus::Overdue->value => EquipmentStatus::Borrowed,
        ];

        $requests = [];

        foreach (BorrowRequestStatus::cases() as $index => $status) {
            $requestNo = 'DEMO-'.$status->value;
            $isOverdue = $status === BorrowRequestStatus::Overdue;
            $isHistorical = in_array($status, [
                BorrowRequestStatus::Returned,
                BorrowRequestStatus::Rejected,
                BorrowRequestStatus::Cancelled,
            ], true);
            $borrowDate = $isOverdue || $isHistorical ? today()->subDays(10) : today()->addDays($index + 1);
            $returnDate = $isOverdue ? today()->subDays(3) : $borrowDate->copy()->addDays(3);

            $borrowRequest = BorrowRequest::query()->updateOrCreate(
                ['request_no' => $requestNo],
                [
                    'user_id' => $borrower->id,
                    'purpose' => 'ข้อมูลตัวอย่างสถานะ '.$status->label(),
                    'usage_location' => 'Demo room',
                    'borrow_date' => $borrowDate,
                    'expected_return_date' => $returnDate,
                    'status' => $status,
                    'note' => 'สร้างโดย WorkflowStatusSeeder',
                    'submitted_at' => $status === BorrowRequestStatus::Draft ? null : now()->subDays(2),
                ],
            );

            $equipment = $this->equipment(
                $category,
                'DEMO-WF-'.$status->value,
                'Workflow '.$status->label(),
                $equipmentStatuses[$status->value] ?? EquipmentStatus::Available,
            );
            BorrowRequestItem::query()->updateOrCreate(
                [
                    'borrow_request_id' => $borrowRequest->id,
                    'equipment_id' => $equipment->id,
                ],
                ['status' => $itemStatuses[$status->value]],
            );

            $requests[$status->value] = $borrowRequest;
        }

        return $requests;
    }

    /** @param array<string, BorrowRequest> $requests */
    private function seedApprovalActions(array $requests, User $approver): void
    {
        $actions = [
            BorrowRequestStatus::Approved->value => ApprovalAction::Approved,
            BorrowRequestStatus::Rejected->value => ApprovalAction::Rejected,
        ];

        foreach ($actions as $requestStatus => $action) {
            $borrowRequest = $requests[$requestStatus];
            BorrowApproval::query()->updateOrCreate(
                [
                    'borrow_request_id' => $borrowRequest->id,
                    'action' => $action->value,
                ],
                [
                    'approver_id' => $approver->id,
                    'comment' => 'ตัวอย่างผลการพิจารณา: '.$action->label(),
                    'acted_at' => now()->subDay(),
                ],
            );
        }
    }

    /** @param array<string, BorrowRequest> $requests */
    private function seedCheckoutHistory(array $requests, User $staff): void
    {
        foreach ([BorrowRequestStatus::Borrowed, BorrowRequestStatus::Overdue] as $status) {
            $item = $requests[$status->value]->items()->firstOrFail();
            EquipmentCheckout::query()->updateOrCreate(
                ['borrow_request_item_id' => $item->id],
                [
                    'checked_out_by' => $staff->id,
                    'checked_out_at' => now()->subDays(5),
                    'condition_before' => 'สภาพปกติ พร้อมใช้งาน',
                    'note' => 'ข้อมูลตัวอย่าง',
                ],
            );
        }
    }

    private function seedReturnStatuses(
        BorrowRequest $returnedRequest,
        EquipmentCategory $category,
        User $staff,
    ): void {
        foreach (ReturnStatus::cases() as $returnStatus) {
            $equipmentCode = $returnStatus === ReturnStatus::Normal
                ? 'DEMO-WF-'.BorrowRequestStatus::Returned->value
                : 'DEMO-RETURN-'.$returnStatus->value;
            $equipment = $this->equipment(
                $category,
                $equipmentCode,
                'Return '.$returnStatus->label(),
                $returnStatus->equipmentStatus(),
            );
            $item = BorrowRequestItem::query()->updateOrCreate(
                [
                    'borrow_request_id' => $returnedRequest->id,
                    'equipment_id' => $equipment->id,
                ],
                ['status' => BorrowItemStatus::Returned],
            );
            EquipmentCheckout::query()->updateOrCreate(
                ['borrow_request_item_id' => $item->id],
                [
                    'checked_out_by' => $staff->id,
                    'checked_out_at' => now()->subDays(10),
                    'condition_before' => 'สภาพปกติก่อนยืม',
                ],
            );
            EquipmentReturn::query()->updateOrCreate(
                ['borrow_request_item_id' => $item->id],
                [
                    'received_by' => $staff->id,
                    'returned_at' => now()->subDays(5),
                    'condition_after' => 'ตัวอย่างผลคืน: '.$returnStatus->label(),
                    'return_status' => $returnStatus,
                    'note' => 'สร้างโดย WorkflowStatusSeeder',
                ],
            );
        }
    }

    /** @param array<string, BorrowRequest> $requests */
    private function seedIncidents(array $requests, EquipmentCategory $category, User $staff): void
    {
        $incidentData = [
            IncidentType::Maintenance->value => [EquipmentStatus::Maintenance, null],
            IncidentType::Damaged->value => [EquipmentStatus::Damaged, null],
            IncidentType::Lost->value => [EquipmentStatus::Lost, null],
            IncidentType::Other->value => [EquipmentStatus::Maintenance, now()->subDay()],
        ];

        foreach (IncidentType::cases() as $type) {
            [$equipmentStatus, $resolvedAt] = $incidentData[$type->value];
            $equipment = $this->equipment(
                $category,
                'DEMO-INCIDENT-'.$type->value,
                'Incident '.$type->label(),
                $equipmentStatus,
            );

            EquipmentIncident::query()->updateOrCreate(
                [
                    'equipment_id' => $equipment->id,
                    'type' => $type->value,
                    'description' => 'ตัวอย่างเหตุขัดข้อง: '.$type->label(),
                ],
                [
                    'borrow_request_id' => $requests[BorrowRequestStatus::Returned->value]->id,
                    'reported_by' => $staff->id,
                    'reported_at' => now()->subDays(2),
                    'resolved_at' => $resolvedAt,
                    'resolved_by' => $resolvedAt ? $staff->id : null,
                    'resolution' => $resolvedAt ? 'ดำเนินการแก้ไขเรียบร้อยแล้ว' : null,
                ],
            );
        }
    }

    /**
     * @param  array<string, BorrowRequest>  $requests
     * @param  array{borrower: User, approver: User, staff: User, admin: User}  $users
     */
    private function seedAuditSamples(array $requests, array $users): void
    {
        $samples = [
            ['borrow.submitted', $users['borrower'], $requests[BorrowRequestStatus::Pending->value]],
            ['approval.approved', $users['approver'], $requests[BorrowRequestStatus::Approved->value]],
            ['checkout.processed', $users['staff'], $requests[BorrowRequestStatus::Borrowed->value]],
            ['return.processed', $users['staff'], $requests[BorrowRequestStatus::Returned->value]],
        ];

        foreach ($samples as [$event, $causer, $subject]) {
            AuditLog::query()->firstOrCreate(
                [
                    'event' => $event,
                    'subject_type' => $subject->getMorphClass(),
                    'subject_id' => $subject->id,
                ],
                [
                    'causer_id' => $causer->id,
                    'new_values' => ['seeded' => true, 'status' => $subject->status->value],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'WorkflowStatusSeeder',
                ],
            );
        }
    }

    private function equipment(
        EquipmentCategory $category,
        string $code,
        string $name,
        EquipmentStatus $status,
    ): Equipment {
        $equipment = Equipment::withTrashed()->firstOrNew(['equipment_code' => $code]);
        $equipment->fill([
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'ข้อมูลตัวอย่างสำหรับทดสอบทุกสถานะ',
            'brand' => 'Demo',
            'model' => $status->value,
            'location' => 'Demo storage',
            'status' => $status,
            'active' => $status !== EquipmentStatus::Inactive,
        ]);
        $equipment->deleted_at = null;
        $equipment->save();

        return $equipment;
    }
}
