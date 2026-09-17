<?php

namespace App\Actions\Borrowing;

use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBorrowRequest
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param array{
     *     purpose: string,
     *     usage_location?: string|null,
     *     borrow_date: string,
     *     expected_return_date: string,
     *     note?: string|null,
     *     equipment_ids: list<int>,
     *     accept_terms: bool|int|string
     * } $attributes
     */
    public function execute(User $borrower, array $attributes): BorrowRequest
    {
        return DB::transaction(function () use ($borrower, $attributes): BorrowRequest {
            if (! filter_var($attributes['accept_terms'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                throw ValidationException::withMessages([
                    'accept_terms' => 'กรุณายอมรับข้อตกลงและเงื่อนไขการยืมก่อนส่งคำขอ',
                ]);
            }

            $equipmentIds = array_values(array_unique($attributes['equipment_ids']));
            $availableEquipment = Equipment::query()
                ->whereKey($equipmentIds)
                ->where('active', true)
                ->where('status', EquipmentStatus::Available->value)
                ->lockForUpdate()
                ->get(['id']);

            if ($availableEquipment->count() !== count($equipmentIds)) {
                throw ValidationException::withMessages([
                    'equipment_ids' => 'มีอุปกรณ์บางรายการไม่พร้อมให้ยืม กรุณาเลือกรายการใหม่',
                ]);
            }

            $borrowRequest = BorrowRequest::query()->create([
                'request_no' => $this->nextRequestNumber(),
                'user_id' => $borrower->id,
                'purpose' => $attributes['purpose'],
                'usage_location' => $attributes['usage_location'] ?? null,
                'borrow_date' => $attributes['borrow_date'],
                'expected_return_date' => $attributes['expected_return_date'],
                'status' => BorrowRequestStatus::Pending,
                'note' => $attributes['note'] ?? null,
                'submitted_at' => now(),
                'terms_accepted_at' => now(),
                'terms_version' => (string) config('borrowing.terms.version'),
            ]);

            $borrowRequest->items()->createMany(array_map(
                fn (int $equipmentId): array => [
                    'equipment_id' => $equipmentId,
                    'status' => BorrowItemStatus::Pending,
                ],
                $equipmentIds,
            ));

            $this->auditLogger->record($borrower, 'borrow.submitted', $borrowRequest, [], [
                'request_no' => $borrowRequest->request_no,
                'status' => BorrowRequestStatus::Pending->value,
                'equipment_ids' => $equipmentIds,
                'terms_version' => $borrowRequest->terms_version,
            ]);

            return $borrowRequest;
        });
    }

    private function nextRequestNumber(): string
    {
        $period = now()->format('Ym');
        $timestamp = now();

        DB::table('request_sequences')->insertOrIgnore([
            'period' => $period,
            'next_number' => 1,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $sequence = DB::table('request_sequences')
            ->where('period', $period)
            ->lockForUpdate()
            ->first();

        $number = (int) $sequence->next_number;

        DB::table('request_sequences')
            ->where('period', $period)
            ->update([
                'next_number' => $number + 1,
                'updated_at' => $timestamp,
            ]);

        return sprintf('BR-%s-%05d', $period, $number);
    }
}
