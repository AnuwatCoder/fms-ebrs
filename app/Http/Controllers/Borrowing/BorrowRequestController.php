<?php

namespace App\Http\Controllers\Borrowing;

use App\Actions\Borrowing\CancelBorrowRequest;
use App\Actions\Borrowing\CreateBorrowRequest;
use App\Actions\Borrowing\UpdateBorrowRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowRequestIndexRequest;
use App\Http\Requests\CancelBorrowRequestRequest;
use App\Http\Requests\EditBorrowRequestRequest;
use App\Http\Requests\ShowBorrowRequestRequest;
use App\Http\Requests\StoreBorrowRequestRequest;
use App\Http\Requests\UpdateBorrowRequestRequest;
use App\Models\BorrowRequest;
use App\Services\Borrowing\BorrowRequestDetailService;
use App\Services\Borrowing\BorrowRequestQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BorrowRequestController extends Controller
{
    public function create(BorrowRequestQueryService $borrowRequests): View
    {
        return view('borrow-requests.create', $borrowRequests->createForm());
    }

    public function store(
        StoreBorrowRequestRequest $request,
        CreateBorrowRequest $createBorrowRequest,
    ): RedirectResponse {
        $submit = $request->isSubmitting();
        $borrowRequest = $createBorrowRequest->execute($request->user(), $request->validated(), $submit);

        return to_route($submit ? 'borrow.mine' : 'borrow.edit', $submit ? [] : $borrowRequest)
            ->with('success', $submit
                ? "ส่งคำขอยืม {$borrowRequest->request_no} เรียบร้อยแล้ว"
                : "บันทึกฉบับร่าง {$borrowRequest->request_no} แล้ว");
    }

    public function edit(
        EditBorrowRequestRequest $request,
        BorrowRequest $borrowRequest,
        BorrowRequestQueryService $borrowRequests,
    ): View {
        return view('borrow-requests.create', $borrowRequests->editForm($borrowRequest));
    }

    public function update(
        UpdateBorrowRequestRequest $request,
        BorrowRequest $borrowRequest,
        UpdateBorrowRequest $updateBorrowRequest,
    ): RedirectResponse {
        $submit = $request->isSubmitting();
        $borrowRequest = $updateBorrowRequest->execute(
            $request->user(),
            $borrowRequest,
            $request->validated(),
            $submit,
        );

        return to_route($submit ? 'borrow.mine' : 'borrow.edit', $submit ? [] : $borrowRequest)
            ->with('success', $submit
                ? "ส่งคำขอยืม {$borrowRequest->request_no} เรียบร้อยแล้ว"
                : "อัปเดตฉบับร่าง {$borrowRequest->request_no} แล้ว");
    }

    public function show(
        ShowBorrowRequestRequest $request,
        BorrowRequest $borrowRequest,
        BorrowRequestDetailService $details,
    ): View {
        return view('borrow-requests.show', $details->detail($borrowRequest));
    }

    public function mine(BorrowRequestIndexRequest $request, BorrowRequestQueryService $borrowRequests): View
    {
        return view('borrow-requests.index', $borrowRequests->mine(
            $request->user(),
            $request->validated(),
        ));
    }

    public function cancel(
        CancelBorrowRequestRequest $request,
        BorrowRequest $borrowRequest,
        CancelBorrowRequest $cancelBorrowRequest,
    ): RedirectResponse {
        $cancelBorrowRequest->execute($request->user(), $borrowRequest);

        return to_route('borrow.mine')
            ->with('success', "ยกเลิกคำขอ {$borrowRequest->request_no} เรียบร้อยแล้ว");
    }

    public function index(BorrowRequestIndexRequest $request, BorrowRequestQueryService $borrowRequests): View
    {
        return view('borrow-requests.index', $borrowRequests->all($request->validated()));
    }
}
