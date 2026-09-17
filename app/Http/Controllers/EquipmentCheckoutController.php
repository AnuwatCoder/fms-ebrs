<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\ProcessEquipmentCheckout;
use App\Enums\BorrowRequestStatus;
use App\Http\Requests\ProcessEquipmentCheckoutRequest;
use App\Models\BorrowRequest;
use App\Services\Operations\OperationsQueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EquipmentCheckoutController extends Controller
{
    public function index(OperationsQueueService $queues): View
    {
        return view('checkouts.index', [
            'borrowRequests' => $queues->checkouts(),
            'approvedStatus' => BorrowRequestStatus::Approved,
            'readyAction' => ProcessEquipmentCheckoutRequest::ACTION_READY,
            'checkoutAction' => ProcessEquipmentCheckoutRequest::ACTION_CHECKOUT,
        ]);
    }

    public function update(
        ProcessEquipmentCheckoutRequest $request,
        BorrowRequest $borrowRequest,
        ProcessEquipmentCheckout $processCheckout,
    ): RedirectResponse {
        if ($request->validated('action') === ProcessEquipmentCheckoutRequest::ACTION_READY) {
            $processCheckout->markReady($request->user(), $borrowRequest);
            $message = "เตรียมคำขอ {$borrowRequest->request_no} พร้อมรับเรียบร้อยแล้ว";
        } else {
            $processCheckout->checkout(
                $request->user(),
                $borrowRequest,
                $request->validated('conditions', []),
                $request->validated('note'),
            );
            $message = "จ่ายอุปกรณ์ตามคำขอ {$borrowRequest->request_no} เรียบร้อยแล้ว";
        }

        return to_route('checkout.index')->with('success', $message);
    }
}
