<?php

namespace App\Http\Controllers;

use App\Actions\Equipment\CreateEquipmentCategory;
use App\Actions\Equipment\DeleteEquipmentCategory;
use App\Actions\Equipment\UpdateEquipmentCategory;
use App\Http\Requests\DeleteEquipmentCategoryRequest;
use App\Http\Requests\EquipmentCategoryIndexRequest;
use App\Http\Requests\StoreEquipmentCategoryRequest;
use App\Http\Requests\UpdateEquipmentCategoryRequest;
use App\Models\EquipmentCategory;
use App\Services\Equipment\EquipmentCategoryQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EquipmentCategoryController extends Controller
{
    public function index(
        EquipmentCategoryIndexRequest $request,
        EquipmentCategoryQueryService $categories,
    ): View {
        return view('categories.index', $categories->index($request->validated()));
    }

    public function create(): View
    {
        return view('categories.form', ['category' => new EquipmentCategory]);
    }

    public function store(
        StoreEquipmentCategoryRequest $request,
        CreateEquipmentCategory $createCategory,
    ): RedirectResponse {
        $category = $createCategory->execute($request->user(), $request->validated());

        return to_route('category.index')->with('success', "เพิ่มหมวดหมู่ {$category->name} เรียบร้อยแล้ว");
    }

    public function edit(EquipmentCategory $category): View
    {
        return view('categories.form', compact('category'));
    }

    public function update(
        UpdateEquipmentCategoryRequest $request,
        EquipmentCategory $category,
        UpdateEquipmentCategory $updateCategory,
    ): RedirectResponse {
        $category = $updateCategory->execute($request->user(), $category, $request->validated());

        return to_route('category.index')->with('success', "อัปเดตหมวดหมู่ {$category->name} เรียบร้อยแล้ว");
    }

    public function destroy(
        DeleteEquipmentCategoryRequest $request,
        EquipmentCategory $category,
        DeleteEquipmentCategory $deleteCategory,
    ): RedirectResponse {
        $deleteCategory->execute($request->user(), $category);

        return to_route('category.index')->with('success', 'ลบหมวดหมู่เรียบร้อยแล้ว');
    }
}
