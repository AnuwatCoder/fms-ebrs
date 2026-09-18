<?php

namespace App\Http\Controllers\Borrowing;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowCalendarRequest;
use App\Services\Borrowing\BorrowCalendarService;
use Illuminate\View\View;

class BorrowCalendarController extends Controller
{
    public function __invoke(BorrowCalendarRequest $request, BorrowCalendarService $calendar): View
    {
        return view('borrow-requests.calendar', $calendar->calendar($request->validated()));
    }
}
