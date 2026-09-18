@extends('layouts.app')

@section('title', 'รายการอุปกรณ์')
@section('page-title', 'รายการอุปกรณ์')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">รายการอุปกรณ์</h1>
        <p class="text-muted mb-0">ค้นหาและตรวจสอบสถานะอุปกรณ์ก่อนสร้างคำขอยืม</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('equipment.create')
            <a href="{{ route('equipment.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i data-lucide="plus" class="lucide-sm"></i>
                เพิ่มอุปกรณ์
            </a>
        @endcan
        @can('borrow.create')
            <a href="{{ route('borrow.create') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                <i data-lucide="circle-plus" class="lucide-sm"></i>
                สร้างคำขอยืม
            </a>
        @endcan
    </div>
</section>

<section class="card p-4">
    <form method="GET" action="{{ route('equipment.index') }}" class="row g-3 align-items-end">
        <div class="col-12 col-lg-5">
            <label for="search" class="form-label">ค้นหา</label>
            <input
                id="search"
                name="search"
                class="form-control"
                value="{{ request('search') }}"
                placeholder="รหัสอุปกรณ์ ชื่อ ยี่ห้อ หรือรุ่น"
            >
        </div>
        <div class="col-12 col-md-5 col-lg-3">
            <label for="category" class="form-label">หมวดหมู่</label>
            <select id="category" name="category" class="form-select">
                <option value="">ทุกหมวดหมู่</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="status" class="form-label">สถานะ</label>
            <select id="status" name="status" class="form-select">
                <option value="">ทุกสถานะ</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3 col-lg-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">ค้นหา</button>
            <a class="btn btn-outline-secondary" href="{{ route('equipment.index') }}" aria-label="ล้างตัวกรอง">
                <i data-lucide="rotate-ccw" class="lucide-sm"></i>
            </a>
        </div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
        <div>
            <h2 class="h5 fw-bold text-slate-800 mb-1">อุปกรณ์ทั้งหมด</h2>
            <p class="text-xs text-slate-500 mb-0">พบ {{ number_format($equipment->total()) }} รายการ</p>
        </div>
        <i data-lucide="monitor" class="lucide-md text-primary"></i>
    </div>

    @if ($equipment->isEmpty())
        <div class="p-5 text-center">
            <div class="icon-circle icon-circle-xl icon-circle-bg-info-soft mx-auto mb-3">
                <i data-lucide="search-x" class="lucide-lg text-info"></i>
            </div>
            <h3 class="h6 fw-semibold mb-1">ไม่พบอุปกรณ์</h3>
            <p class="text-muted small mb-0">ลองเปลี่ยนคำค้นหาหรือตัวกรองสถานะ</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>อุปกรณ์</th>
                        <th>หมวดหมู่</th>
                        <th>สถานที่</th>
                        <th>สถานะ</th>
                        <th class="text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipment as $item)
                        <tr>
                            <td class="fw-semibold">
                                <a href="{{ route('equipment.show', $item) }}" class="text-primary text-decoration-none">{{ $item->equipment_code }}</a>
                            </td>
                            <td>
                                <a href="{{ route('equipment.show', $item) }}" class="fw-semibold text-slate-800 text-decoration-none">{{ $item->name }}</a>
                                <div class="text-xs text-slate-500">
                                    {{ collect([$item->brand, $item->model])->filter()->join(' · ') ?: 'ไม่ระบุยี่ห้อ/รุ่น' }}
                                </div>
                            </td>
                            <td>{{ $item->category->name }}</td>
                            <td>{{ $item->location ?: '—' }}</td>
                            <td><span class="badge {{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    @can('view', $item)
                                        <a href="{{ route('equipment.show', $item) }}" class="btn btn-sm btn-outline-secondary" aria-label="ดู {{ $item->name }}">
                                            <i data-lucide="eye" class="lucide-sm"></i>
                                        </a>
                                    @endcan
                                    @can('update', $item)
                                        <a href="{{ route('equipment.edit', $item) }}" class="btn btn-sm btn-outline-primary" aria-label="แก้ไข {{ $item->name }}">
                                            <i data-lucide="pencil" class="lucide-sm"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('equipment.destroy', $item) }}" onsubmit="return confirm('ยืนยันการลบอุปกรณ์ {{ $item->equipment_code }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="ลบ {{ $item->name }}">
                                                <i data-lucide="trash-2" class="lucide-sm"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($equipment->hasPages())
            <div class="p-4 border-top">{{ $equipment->links() }}</div>
        @endif
    @endif
</section>
@endsection
