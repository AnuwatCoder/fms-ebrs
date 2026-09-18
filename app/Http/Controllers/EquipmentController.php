<?php

namespace App\Http\Controllers;

use App\Actions\Equipment\CreateEquipment;
use App\Actions\Equipment\DeleteEquipment;
use App\Actions\Equipment\UpdateEquipment;
use App\Http\Requests\DeleteEquipmentRequest;
use App\Http\Requests\EditEquipmentRequest;
use App\Http\Requests\EquipmentIndexRequest;
use App\Http\Requests\ShowEquipmentRequest;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Equipment;
use App\Services\Equipment\EquipmentCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(EquipmentIndexRequest $request, EquipmentCatalogService $catalog): View
    {
        return view('equipment.index', $catalog->index($request->validated()));
    }

    public function create(EquipmentCatalogService $catalog): View
    {
        return view('equipment.form', $catalog->createForm());
    }

    public function store(StoreEquipmentRequest $request, CreateEquipment $createEquipment): RedirectResponse
    {
        $equipment = $createEquipment->execute($request->user(), $request->validated());

        return to_route('equipment.index')
            ->with('success', "เพิ่มอุปกรณ์ {$equipment->equipment_code} เรียบร้อยแล้ว");
    }

    public function show(
        ShowEquipmentRequest $request,
        Equipment $equipment,
        EquipmentCatalogService $catalog,
    ): View {
        return view('equipment.show', $catalog->detail($equipment));
    }

    public function edit(
        EditEquipmentRequest $request,
        Equipment $equipment,
        EquipmentCatalogService $catalog,
    ): View {
        return view('equipment.form', $catalog->editForm($equipment));
    }

    public function update(
        UpdateEquipmentRequest $request,
        Equipment $equipment,
        UpdateEquipment $updateEquipment,
    ): RedirectResponse {
        $equipment = $updateEquipment->execute($request->user(), $equipment, $request->validated());

        return to_route('equipment.show', $equipment)
            ->with('success', "อัปเดตอุปกรณ์ {$equipment->equipment_code} เรียบร้อยแล้ว");
    }

    public function destroy(
        DeleteEquipmentRequest $request,
        Equipment $equipment,
        DeleteEquipment $deleteEquipment,
    ): RedirectResponse {
        $equipmentCode = $equipment->equipment_code;
        $deleteEquipment->execute($request->user(), $equipment);

        return to_route('equipment.index')->with('success', "ลบอุปกรณ์ {$equipmentCode} เรียบร้อยแล้ว");
    }
}
