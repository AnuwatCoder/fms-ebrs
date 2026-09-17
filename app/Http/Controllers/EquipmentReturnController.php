<?php

namespace App\Http\Controllers;

use App\Actions\Returns\ProcessEquipmentReturn;
use App\Http\Requests\ProcessEquipmentReturnRequest;
use App\Models\BorrowRequest;
use App\Services\Operations\OperationsQueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EquipmentReturnController extends Controller
{
    public function index(OperationsQueueService $queues): View
    {
        return view('returns.index', $queues->returns());
    }

    public function update(
        ProcessEquipmentReturnRequest $request,
        BorrowRequest $borrowRequest,
        ProcessEquipmentReturn $processReturn,
    ): RedirectResponse {
        $processReturn->execute(
            $request->user(),
            $borrowRequest,
            $request->validated('items'),
        );

        return to_route('return.index')
            ->with('success', "รับคืนอุปกรณ์ตามคำขอ {$borrowRequest->request_no} เรียบร้อยแล้ว");
    }
}
