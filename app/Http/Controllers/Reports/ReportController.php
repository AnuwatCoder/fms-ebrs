<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\BorrowingReportRequest;
use App\Http\Requests\Reports\DamageReportRequest;
use App\Http\Requests\Reports\EquipmentReportRequest;
use App\Http\Requests\Reports\OverdueReportRequest;
use App\Services\Reports\ReportQueryService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function equipment(EquipmentReportRequest $request, ReportQueryService $reports): View
    {
        return view('reports.equipment', $reports->equipment($request->validated()));
    }

    public function borrowing(BorrowingReportRequest $request, ReportQueryService $reports): View
    {
        return view('reports.borrowing', $reports->borrowing($request->validated()));
    }

    public function overdue(OverdueReportRequest $request, ReportQueryService $reports): View
    {
        return view('reports.overdue', $reports->overdue($request->validated()));
    }

    public function damage(DamageReportRequest $request, ReportQueryService $reports): View
    {
        return view('reports.damage', $reports->damage($request->validated()));
    }
}
