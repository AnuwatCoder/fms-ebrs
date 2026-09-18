@extends('layouts.app')

@section('title', $equipment->name)
@section('page-title', 'รายละเอียดอุปกรณ์')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h1 class="h3 fw-bold text-slate-800 mb-0">{{ $equipment->name }}</h1>
            <span class="badge {{ $equipment->status->badgeClass() }}">{{ $equipment->status->label() }}</span>
        </div>
        <p class="text-muted mb-0">{{ $equipment->equipment_code }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i data-lucide="arrow-left" class="lucide-sm"></i>
            กลับไปรายการ
        </a>
        @can('update', $equipment)
            <a href="{{ route('equipment.edit', $equipment) }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i data-lucide="pencil" class="lucide-sm"></i>
                แก้ไข
            </a>
        @endcan
        @can('delete', $equipment)
            <form method="POST" action="{{ route('equipment.destroy', $equipment) }}" onsubmit="return confirm('ยืนยันการลบอุปกรณ์ {{ $equipment->equipment_code }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger d-inline-flex align-items-center gap-2">
                    <i data-lucide="trash-2" class="lucide-sm"></i>
                    ลบ
                </button>
            </form>
        @endcan
    </div>
</section>

<section class="row g-4">
    <div class="col-12 col-xl-8">
        <article class="card h-100 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
                <h2 class="h5 fw-bold text-slate-800 mb-0">ข้อมูลอุปกรณ์</h2>
                <i data-lucide="monitor" class="lucide-md text-primary"></i>
            </div>
            <div class="p-4">
                <dl class="row g-4 mb-0">
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">รหัสอุปกรณ์</dt>
                        <dd class="fw-semibold text-primary mb-0">{{ $equipment->equipment_code }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">เลขครุภัณฑ์</dt>
                        <dd class="mb-0">{{ $equipment->asset_number ?: '—' }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">หมวดหมู่</dt>
                        <dd class="mb-0">{{ $equipment->category->name }} <span class="text-muted">({{ $equipment->category->code }})</span></dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">สถานที่จัดเก็บ</dt>
                        <dd class="mb-0">{{ $equipment->location ?: '—' }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">ยี่ห้อ / รุ่น</dt>
                        <dd class="mb-0">{{ collect([$equipment->brand, $equipment->model])->filter()->join(' · ') ?: '—' }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">Serial number</dt>
                        <dd class="mb-0">{{ $equipment->serial_number ?: '—' }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">วันที่ซื้อ</dt>
                        <dd class="mb-0">{{ $equipment->purchase_date?->format('d/m/Y') ?: '—' }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">การเปิดใช้งาน</dt>
                        <dd class="mb-0">
                            <span class="badge {{ $equipment->active ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                {{ $equipment->active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </span>
                        </dd>
                    </div>
                    <div class="col-12">
                        <dt class="text-xs text-slate-500 mb-1">รายละเอียด</dt>
                        <dd class="mb-0">{{ $equipment->description ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </article>
    </div>
    <div class="col-12 col-xl-4">
        <article class="card h-100">
            <div class="p-4 border-bottom">
                <h2 class="h5 fw-bold text-slate-800 mb-0">สรุปการใช้งาน</h2>
            </div>
            <div class="p-4 d-grid gap-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-circle icon-circle-sm icon-circle-bg-info-soft"><i data-lucide="clipboard-list" class="lucide-sm text-info"></i></div>
                        <span>ประวัติการยืม</span>
                    </div>
                    <strong>{{ number_format($equipment->borrow_items_count) }}</strong>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-circle icon-circle-sm icon-circle-bg-warning-soft"><i data-lucide="triangle-alert" class="lucide-sm text-warning"></i></div>
                        <span>เหตุขัดข้อง</span>
                    </div>
                    <strong>{{ number_format($equipment->incidents_count) }}</strong>
                </div>
                @if ($equipment->borrow_items_count > 0 || $equipment->incidents_count > 0)
                    <div class="alert alert-info small mb-0" role="status">
                        อุปกรณ์นี้มีประวัติอ้างอิง จึงไม่สามารถลบได้ แต่สามารถปิดใช้งานจากหน้าแก้ไข
                    </div>
                @endif
            </div>
        </article>
    </div>
</section>
@endsection
