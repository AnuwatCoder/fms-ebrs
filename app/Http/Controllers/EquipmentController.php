<?php

namespace App\Http\Controllers;

use App\Actions\Equipment\CreateEquipment;
use App\Http\Requests\EquipmentIndexRequest;
use App\Http\Requests\StoreEquipmentRequest;
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
        return view('equipment.create', $catalog->createForm());
    }

    public function store(StoreEquipmentRequest $request, CreateEquipment $createEquipment): RedirectResponse
    {
        $equipment = $createEquipment->execute($request->user(), $request->validated());

        return to_route('equipment.index')
            ->with('success', "เพิ่มอุปกรณ์ {$equipment->equipment_code} เรียบร้อยแล้ว");
    }
}
