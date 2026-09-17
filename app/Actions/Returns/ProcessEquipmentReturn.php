<?php

namespace App\Actions\Returns;

use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\IncidentType;
use App\Enums\ReturnStatus;
use App\Models\BorrowRequest;
use App\Models\BorrowRequestItem;
use App\Models\Equipment;
use App\Models\EquipmentIncident;
use App\Models\EquipmentReturn;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcessEquipmentReturn
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<int|string, array{return_status: string, condition_after: string, note?: string|null}>  $returnData
     */
    public function execute(User $staff, BorrowRequest $borrowRequest, array $returnData): BorrowRequest
    {
        return DB::transaction(function () use ($staff, $borrowRequest, $returnData): BorrowRequest {
            $lockedRequest = BorrowRequest::query()
                ->whereKey($borrowRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedRequest->status, [BorrowRequestStatus::Borrowed, BorrowRequestStatus::Overdue], true)) {
                throw ValidationException::withMessages([
                    'items' => 'คำขอนี้ไม่อยู่ในสถานะที่รับคืนได้',
                ]);
            }

            $items = BorrowRequestItem::query()
                ->where('borrow_request_id', $lockedRequest->id)
                ->lockForUpdate()
                ->get();
            $normalized = collect($returnData)->mapWithKeys(
                fn (array $data, int|string $itemId): array => [(int) $itemId => $data],
            );

            if (
                $items->isEmpty()
                || $items->contains(fn (BorrowRequestItem $item): bool => ! $normalized->has($item->id))
                || $items->contains(fn (BorrowRequestItem $item): bool => $item->status !== BorrowItemStatus::Borrowed)
                || EquipmentReturn::query()->whereIn('borrow_request_item_id', $items->pluck('id'))->exists()
            ) {
                throw ValidationException::withMessages([
                    'items' => 'รายการอุปกรณ์ไม่พร้อมรับคืนหรือเคยรับคืนไปแล้ว กรุณาตรวจสอบอีกครั้ง',
                ]);
            }

            $equipment = Equipment::query()
                ->whereKey($items->pluck('equipment_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($equipment->count() !== $items->count()) {
                throw ValidationException::withMessages([
                    'items' => 'ไม่พบข้อมูลอุปกรณ์บางรายการ กรุณาติดต่อผู้ดูแลระบบ',
                ]);
            }

            foreach ($items as $item) {
                $data = $normalized->get($item->id);
                $returnStatus = ReturnStatus::from($data['return_status']);
                $condition = trim($data['condition_after']);

                $item->equipmentReturn()->create([
                    'received_by' => $staff->id,
                    'returned_at' => now(),
                    'condition_after' => $condition,
                    'return_status' => $returnStatus,
                    'note' => $data['note'] ?? null,
                ]);
                $item->update(['status' => BorrowItemStatus::Returned]);
                $equipment->get($item->equipment_id)->update([
                    'status' => $returnStatus->equipmentStatus(),
                ]);

                if ($returnStatus !== ReturnStatus::Normal) {
                    $hasOpenIncident = EquipmentIncident::query()
                        ->where('equipment_id', $item->equipment_id)
                        ->whereNull('resolved_at')
                        ->lockForUpdate()
                        ->exists();

                    if (! $hasOpenIncident) {
                        $lockedRequest->incidents()->create([
                            'equipment_id' => $item->equipment_id,
                            'type' => match ($returnStatus) {
                                ReturnStatus::Lost => IncidentType::Lost,
                                ReturnStatus::MaintenanceRequired => IncidentType::Maintenance,
                                default => IncidentType::Damaged,
                            },
                            'description' => $returnStatus->label().': '.$condition,
                            'reported_by' => $staff->id,
                            'reported_at' => now(),
                        ]);
                    }
                }
            }

            $previousStatus = $lockedRequest->status;
            $lockedRequest->update(['status' => BorrowRequestStatus::Returned]);

            $this->auditLogger->record(
                $staff,
                'return.processed',
                $lockedRequest,
                ['status' => $previousStatus->value],
                [
                    'status' => BorrowRequestStatus::Returned->value,
                    'results' => $normalized->pluck('return_status')->all(),
                ],
            );

            return $lockedRequest->refresh();
        });
    }
}
