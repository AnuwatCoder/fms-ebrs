<?php

namespace App\Http\Controllers;

use App\Actions\Maintenance\ReportEquipmentIncident;
use App\Actions\Maintenance\ResolveEquipmentIncident;
use App\Enums\EquipmentStatus;
use App\Enums\IncidentType;
use App\Http\Requests\MaintenanceIndexRequest;
use App\Http\Requests\ResolveEquipmentIncidentRequest;
use App\Http\Requests\StoreEquipmentIncidentRequest;
use App\Models\EquipmentIncident;
use App\Services\Maintenance\MaintenanceQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(MaintenanceIndexRequest $request, MaintenanceQueryService $maintenance): View
    {
        return view('maintenance.index', $maintenance->index($request->validated()));
    }

    public function create(MaintenanceQueryService $maintenance): View
    {
        return view('maintenance.create', $maintenance->createForm());
    }

    public function store(
        StoreEquipmentIncidentRequest $request,
        ReportEquipmentIncident $reportIncident,
    ): RedirectResponse {
        $incident = $reportIncident->execute(
            $request->user(),
            (int) $request->validated('equipment_id'),
            IncidentType::from($request->validated('type')),
            $request->validated('description'),
        );

        return to_route('maintenance.index')
            ->with('success', "บันทึกเหตุขัดข้องของ {$incident->equipment->equipment_code} เรียบร้อยแล้ว");
    }

    public function resolve(EquipmentIncident $incident, MaintenanceQueryService $maintenance): View
    {
        return view('maintenance.resolve', $maintenance->resolveForm($incident));
    }

    public function update(
        ResolveEquipmentIncidentRequest $request,
        EquipmentIncident $incident,
        ResolveEquipmentIncident $resolveIncident,
    ): RedirectResponse {
        $resolveIncident->execute(
            $request->user(),
            $incident,
            $request->validated('resolution'),
            EquipmentStatus::from($request->validated('equipment_status')),
        );

        return to_route('maintenance.index')->with('success', 'ปิดเหตุขัดข้องเรียบร้อยแล้ว');
    }
}
