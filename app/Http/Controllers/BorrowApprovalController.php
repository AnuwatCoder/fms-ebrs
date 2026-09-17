<?php

namespace App\Http\Controllers;

use App\Actions\Borrowing\ProcessBorrowApproval;
use App\Enums\ApprovalAction;
use App\Http\Requests\ProcessBorrowApprovalRequest;
use App\Models\BorrowRequest;
use App\Services\Operations\OperationsQueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BorrowApprovalController extends Controller
{
    public function index(OperationsQueueService $queues): View
    {
        return view('approvals.index', [
            'borrowRequests' => $queues->approvals(),
            'approveAction' => ApprovalAction::Approved,
            'rejectAction' => ApprovalAction::Rejected,
        ]);
    }

    public function update(
        ProcessBorrowApprovalRequest $request,
        BorrowRequest $borrowRequest,
        ProcessBorrowApproval $processBorrowApproval,
    ): RedirectResponse {
        $action = ApprovalAction::from($request->validated('action'));

        $processBorrowApproval->execute(
            $request->user(),
            $borrowRequest,
            $action,
            $request->validated('comment'),
        );

        $message = $action === ApprovalAction::Approved
            ? "อนุมัติคำขอ {$borrowRequest->request_no} เรียบร้อยแล้ว"
            : "ไม่อนุมัติคำขอ {$borrowRequest->request_no} เรียบร้อยแล้ว";

        return to_route('approval.index')->with('success', $message);
    }
}
