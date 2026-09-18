@extends('layouts.app')

@php($editing = $equipment->exists)

@section('title', $editing ? 'แก้ไขอุปกรณ์' : 'เพิ่มอุปกรณ์')
@section('page-title', $editing ? 'แก้ไขอุปกรณ์' : 'เพิ่มอุปกรณ์')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">{{ $editing ? 'แก้ไขอุปกรณ์' : 'เพิ่มอุปกรณ์' }}</h1>
        <p class="text-muted mb-0">บันทึกข้อมูลประจำตัว สถานที่ และสถานะการเปิดใช้งานของอุปกรณ์</p>
    </div>
    <a href="{{ $editing ? route('equipment.show', $equipment) : route('equipment.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i data-lucide="arrow-left" class="lucide-sm"></i>
        {{ $editing ? 'กลับไปหน้ารายละเอียด' : 'กลับไปรายการ' }}
    </a>
</section>

<form method="POST" action="{{ $editing ? route('equipment.update', $equipment) : route('equipment.store') }}" class="card p-4">
    @csrf
    @if ($editing)
        @method('PATCH')
    @endif

    <div class="d-flex align-items-center gap-2 mb-4">
        <div class="icon-circle icon-circle-sm icon-circle-bg-primary-soft">
            <i data-lucide="{{ $editing ? 'monitor-cog' : 'monitor-up' }}" class="lucide-sm text-primary"></i>
        </div>
        <h2 class="h5 fw-bold text-slate-800 mb-0">ข้อมูลอุปกรณ์</h2>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <label class="form-label">รหัสอุปกรณ์</label>
            @if ($editing)
                <div class="form-control bg-light-subtle fw-semibold text-primary">{{ $equipment->equipment_code }}</div>
            @else
                <div class="form-control bg-light-subtle text-muted d-flex align-items-center gap-2" data-auto-code="equipment">
                    <i data-lucide="sparkles" class="lucide-xs text-primary"></i>
                    ระบบจะสร้างให้อัตโนมัติ เช่น EQ-000001
                </div>
            @endif
        </div>
        <div class="col-12 col-md-6">
            <label for="asset_number" class="form-label">เลขครุภัณฑ์</label>
            <input id="asset_number" name="asset_number" class="form-control @error('asset_number') is-invalid @enderror" value="{{ old('asset_number', $equipment->asset_number) }}">
            @error('asset_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-8">
            <label for="name" class="form-label">ชื่ออุปกรณ์ <span class="text-danger">*</span></label>
            <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $equipment->name) }}" required autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-4">
            <label for="category_id" class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">เลือกหมวดหมู่</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $equipment->category_id) === (string) $category->id)>
                        {{ $category->name }}{{ $category->active ? '' : ' (ปิดใช้งาน)' }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-4">
            <label for="brand" class="form-label">ยี่ห้อ</label>
            <input id="brand" name="brand" class="form-control @error('brand') is-invalid @enderror" value="{{ old('brand', $equipment->brand) }}">
            @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-4">
            <label for="model" class="form-label">รุ่น</label>
            <input id="model" name="model" class="form-control @error('model') is-invalid @enderror" value="{{ old('model', $equipment->model) }}">
            @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-4">
            <label for="serial_number" class="form-label">Serial number</label>
            <input id="serial_number" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" value="{{ old('serial_number', $equipment->serial_number) }}">
            @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-4">
            <label for="location" class="form-label">สถานที่จัดเก็บ</label>
            <input id="location" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $equipment->location) }}">
            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-4">
            <label for="purchase_date" class="form-label">วันที่ซื้อ</label>
            <input id="purchase_date" type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" max="{{ today()->format('Y-m-d') }}" value="{{ old('purchase_date', $equipment->purchase_date?->format('Y-m-d')) }}">
            @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-4">
            <label for="status" class="form-label">สถานะอุปกรณ์</label>
            @if ($editing)
                <div class="form-control bg-light-subtle">
                    <span class="badge {{ $equipment->status->badgeClass() }}">{{ $equipment->status->label() }}</span>
                </div>
                <div class="form-text">สถานะจะเปลี่ยนตามขั้นตอนการยืม คืน และซ่อมบำรุง</div>
            @else
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', \App\Enums\EquipmentStatus::Available->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @endif
        </div>
        <div class="col-12 col-md-8">
            <label for="description" class="form-label">รายละเอียด</label>
            <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $equipment->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <input type="hidden" name="active" value="0">
            <div class="form-check">
                <input id="active" type="checkbox" name="active" value="1" class="form-check-input" @checked((string) old('active', $editing ? (int) $equipment->active : 1) === '1')>
                <label for="active" class="form-check-label">เปิดใช้งานและแสดงอุปกรณ์ในระบบ</label>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
        <a href="{{ $editing ? route('equipment.show', $equipment) : route('equipment.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i data-lucide="save" class="lucide-sm"></i>
            {{ $editing ? 'บันทึกการแก้ไข' : 'บันทึกอุปกรณ์' }}
        </button>
    </div>
</form>
@endsection
