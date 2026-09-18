<?php

namespace App\Http\Controllers\Borrowing;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowRequestAvailabilityRequest;
use App\Http\Resources\EquipmentAvailabilityResource;
use App\Services\Borrowing\EquipmentAvailabilityService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BorrowRequestAvailabilityController extends Controller
{
    public function __invoke(
        BorrowRequestAvailabilityRequest $request,
        EquipmentAvailabilityService $availability,
    ): AnonymousResourceCollection {
        $equipment = $availability->availableBetween(
            $request->date('borrow_date'),
            $request->date('expected_return_date'),
        );

        return EquipmentAvailabilityResource::collection($equipment)->additional([
            'meta' => [
                'count' => $equipment->count(),
                'message' => $equipment->isEmpty()
                    ? 'ไม่พบอุปกรณ์ว่างในช่วงวันที่เลือก กรุณาเปลี่ยนช่วงวันที่หรือติดต่อเจ้าหน้าที่'
                    : 'พบอุปกรณ์ว่าง '.$equipment->count().' รายการ',
            ],
        ]);
    }
}
