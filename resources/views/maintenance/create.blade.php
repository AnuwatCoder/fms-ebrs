@extends('layouts.app')

@section('title', 'รายงานเหตุขัดข้อง')
@section('page-title', 'รายงานเหตุขัดข้อง')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">รายงานเหตุขัดข้อง</h1>
        <p class="text-muted mb-0">บันทึกปัญหาและนำอุปกรณ์ออกจากสถานะพร้อมยืมโดยอัตโนมัติ</p>
    </div>
    <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i data-lucide="arrow-left" class="lucide-sm"></i>
        กลับไปหน้ารายการ
    </a>
</section>

<form method="POST" action="{{ route('maintenance.store') }}" class="card p-4">
    @csrf

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <label for="equipment_id" class="form-label">อุปกรณ์ <span class="text-danger">*</span></label>
            <select id="equipment_id" name="equipment_id" class="form-select @error('equipment_id') is-invalid @enderror" required autofocus>
                <option value="">เลือกอุปกรณ์</option>
                @foreach ($equipment as $item)
                    <option value="{{ $item->id }}" @selected((string) old('equipment_id') === (string) $item->id)>
                        {{ $item->equipment_code }} — {{ $item->name }} ({{ $item->category->name }} / {{ $item->status->label() }})
                    </option>
                @endforeach
            </select>
            @error('equipment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-lg-4">
            <label for="type" class="form-label">ประเภทเหตุ <span class="text-danger">*</span></label>
            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                <option value="">เลือกประเภท</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label for="description" class="form-label">รายละเอียดเหตุขัดข้อง <span class="text-danger">*</span></label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="6" maxlength="5000" required>{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
        <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
        <button class="btn btn-primary" type="submit">บันทึกเหตุขัดข้อง</button>
    </div>
</form>
@endsection
