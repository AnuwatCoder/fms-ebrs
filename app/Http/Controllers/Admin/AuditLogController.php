<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditLogIndexRequest;
use App\Services\Administration\AuditLogQueryService;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request, AuditLogQueryService $auditLogs): View
    {
        return view('audit.index', $auditLogs->index($request->validated()));
    }
}
