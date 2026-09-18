<?php

namespace App\Services\Borrowing;

use App\Enums\ApprovalAction;
use App\Models\BorrowRequest;
use Illuminate\Support\Collection;

class BorrowRequestDetailService
{
    /** @return array<string, mixed> */
    public function detail(BorrowRequest $borrowRequest): array
    {
        $borrowRequest->load([
            'borrower:id,name,email,username',
            'items.equipment.category:id,name,code',
            'items.checkout.staff:id,name',
            'items.equipmentReturn.receiver:id,name',
            'approvals.approver:id,name',
            'incidents.equipment:id,public_id,equipment_code,name',
            'incidents.reporter:id,name',
            'incidents.resolver:id,name',
            'auditLogs.causer:id,name',
        ]);

        return [
            'borrowRequest' => $borrowRequest,
            'timeline' => $this->timeline($borrowRequest),
            'auditLogs' => $borrowRequest->auditLogs->sortByDesc('created_at')->values(),
        ];
    }

    /** @return Collection<int, array{title: string, description: string, occurred_at: mixed, actor: string|null, icon: string, tone: string}> */
    private function timeline(BorrowRequest $borrowRequest): Collection
    {
        $events = collect();

        $events->push([
            'title' => 'สร้างคำขอยืม',
            'description' => "สร้างคำขอ {$borrowRequest->request_no}",
            'occurred_at' => $borrowRequest->created_at,
            'actor' => $borrowRequest->borrower->name,
            'icon' => 'file-plus-2',
            'tone' => 'secondary',
        ]);

        if ($borrowRequest->submitted_at !== null) {
            $events->push([
                'title' => 'ส่งคำขออนุมัติ',
                'description' => 'ส่งคำขอเข้าสู่กระบวนการพิจารณา',
                'occurred_at' => $borrowRequest->submitted_at,
                'actor' => $borrowRequest->borrower->name,
                'icon' => 'send',
                'tone' => 'primary',
            ]);
        }

        foreach ($borrowRequest->approvals as $approval) {
            $events->push([
                'title' => $approval->action->label(),
                'description' => $approval->comment ?: 'ไม่มีความเห็นเพิ่มเติม',
                'occurred_at' => $approval->acted_at,
                'actor' => $approval->approver->name,
                'icon' => $approval->action === ApprovalAction::Approved ? 'badge-check' : 'circle-x',
                'tone' => $approval->action === ApprovalAction::Approved ? 'success' : 'danger',
            ]);
        }

        foreach ($borrowRequest->items as $item) {
            if ($item->checkout !== null) {
                $events->push([
                    'title' => 'จ่ายอุปกรณ์ '.$item->equipment->equipment_code,
                    'description' => $item->checkout->condition_before,
                    'occurred_at' => $item->checkout->checked_out_at,
                    'actor' => $item->checkout->staff->name,
                    'icon' => 'scan-line',
                    'tone' => 'info',
                ]);
            }

            if ($item->equipmentReturn !== null) {
                $events->push([
                    'title' => 'รับคืนอุปกรณ์ '.$item->equipment->equipment_code,
                    'description' => $item->equipmentReturn->return_status->label().': '.$item->equipmentReturn->condition_after,
                    'occurred_at' => $item->equipmentReturn->returned_at,
                    'actor' => $item->equipmentReturn->receiver->name,
                    'icon' => 'package-check',
                    'tone' => 'success',
                ]);
            }
        }

        foreach ($borrowRequest->incidents as $incident) {
            $events->push([
                'title' => 'แจ้งเหตุ '.$incident->equipment->equipment_code,
                'description' => $incident->description,
                'occurred_at' => $incident->reported_at,
                'actor' => $incident->reporter->name,
                'icon' => 'triangle-alert',
                'tone' => 'danger',
            ]);

            if ($incident->resolved_at !== null) {
                $events->push([
                    'title' => 'ปิดเหตุ '.$incident->equipment->equipment_code,
                    'description' => $incident->resolution ?: 'ปิดเหตุเรียบร้อยแล้ว',
                    'occurred_at' => $incident->resolved_at,
                    'actor' => $incident->resolver?->name,
                    'icon' => 'shield-check',
                    'tone' => 'success',
                ]);
            }
        }

        return $events
            ->filter(fn (array $event): bool => $event['occurred_at'] !== null)
            ->sortByDesc('occurred_at')
            ->values();
    }
}
