<?php

namespace App\Http\Controllers;

use App\Actions\Borrowing\CancelBorrowRequest;
use App\Actions\Borrowing\CreateBorrowRequest;
use App\Http\Requests\BorrowRequestIndexRequest;
use App\Http\Requests\CancelBorrowRequestRequest;
use App\Http\Requests\StoreBorrowRequestRequest;
use App\Models\BorrowRequest;
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
        $borrowRequest = $createBorrowRequest->execute($request->user(), $request->validated());

        return to_route('borrow.mine')
            ->with('success', "ส่งคำขอยืม {$borrowRequest->request_no} เรียบร้อยแล้ว");
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
