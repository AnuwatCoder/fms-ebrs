@extends('layouts.app')

@section('title', 'ปิดเหตุขัดข้อง')
@section('page-title', 'ปิดเหตุขัดข้อง')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">ปิดเหตุขัดข้อง</h1>
        <p class="text-muted mb-0">บันทึกผลการดำเนินการและกำหนดสถานะล่าสุดของอุปกรณ์</p>
    </div>
    <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i data-lucide="arrow-left" class="lucide-sm"></i>
        กลับไปหน้ารายการ
    </a>
</section>

<section class="card p-4">
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="text-xs text-muted mb-1">อุปกรณ์</div>
            <div class="fw-semibold text-primary">{{ $incident->equipment->equipment_code }}</div>
            <div>{{ $incident->equipment->name }}</div>
        </div>
        <div class="col-12 col-md-3">
            <div class="text-xs text-muted mb-1">ประเภทเหตุ</div>
            <span class="badge {{ $incident->type->badgeClass() }}">{{ $incident->type->label() }}</span>
        </div>
        <div class="col-12 col-md-5">
            <div class="text-xs text-muted mb-1">รายงานโดย</div>
            <div>{{ $incident->reporter->name }} · {{ $incident->reported_at->format('d/m/Y H:i') }}</div>
        </div>
        <div class="col-12">
            <div class="text-xs text-muted mb-1">รายละเอียด</div>
            <div class="p-3 bg-light rounded">{{ $incident->description }}</div>
        </div>
    </div>
</section>

<form method="POST" action="{{ route('maintenance.update', $incident) }}" class="card p-4">
    @csrf
    @method('PATCH')

    <div class="row g-4">
        <div class="col-12 col-md-5">
            <label for="equipment_status" class="form-label">สถานะอุปกรณ์หลังดำเนินการ <span class="text-danger">*</span></label>
            <select id="equipment_status" name="equipment_status" class="form-select @error('equipment_status') is-invalid @enderror" required>
                @foreach ($equipmentStatuses as $status)
                    <option value="{{ $status->value }}" @selected(old('equipment_status', $equipmentStatuses[0]->value) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            @error('equipment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label for="resolution" class="form-label">ผลการดำเนินการ <span class="text-danger">*</span></label>
            <textarea id="resolution" name="resolution" class="form-control @error('resolution') is-invalid @enderror" rows="6" maxlength="5000" required autofocus>{{ old('resolution') }}</textarea>
            @error('resolution')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
        <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
        <button class="btn btn-primary" type="submit">บันทึกและปิดเหตุ</button>
    </div>
</form>
@endsection
