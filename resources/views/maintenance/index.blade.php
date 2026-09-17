@extends('layouts.app')

@section('title', 'บำรุงรักษาและเหตุขัดข้อง')
@section('page-title', 'บำรุงรักษาและเหตุขัดข้อง')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">บำรุงรักษาและเหตุขัดข้อง</h1>
        <p class="text-muted mb-0">ติดตามอุปกรณ์ที่ต้องซ่อม ชำรุด สูญหาย หรือมีปัญหาจากการใช้งาน</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="badge badge-soft-warning px-3 py-2">รอดำเนินการ {{ number_format($openCount) }}</span>
        <span class="badge badge-soft-success px-3 py-2">แก้ไขแล้ว {{ number_format($resolvedCount) }}</span>
        @can('maintenance.manage')
            <a href="{{ route('maintenance.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i data-lucide="triangle-alert" class="lucide-sm"></i>
                รายงานเหตุขัดข้อง
            </a>
        @endcan
    </div>
</section>

<section class="card p-4">
    <form method="GET" action="{{ route('maintenance.index') }}" class="row g-3 align-items-end">
        <div class="col-12 col-lg-5">
            <label for="search" class="form-label">ค้นหา</label>
            <input id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="รหัส ชื่ออุปกรณ์ หรือรายละเอียดเหตุ">
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <label for="type" class="form-label">ประเภท</label>
            <select id="type" name="type" class="form-select">
                <option value="">ทุกประเภท</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="state" class="form-label">การดำเนินการ</label>
            <select id="state" name="state" class="form-select">
                <option value="">ทั้งหมด</option>
                @foreach ($states as $state)
                    <option value="{{ $state->value }}" @selected(request('state') === $state->value)>{{ $state->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">ค้นหา</button>
            <a class="btn btn-outline-secondary" href="{{ route('maintenance.index') }}" aria-label="ล้างตัวกรอง">
                <i data-lucide="rotate-ccw" class="lucide-sm"></i>
            </a>
        </div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>วันที่รายงาน</th>
                    <th>อุปกรณ์</th>
                    <th>ประเภท</th>
                    <th>รายละเอียด</th>
                    <th>ผู้รายงาน</th>
                    <th>สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($incidents as $incident)
                    @php($state = $incident->state())
                    <tr>
                        <td>{{ $incident->reported_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="fw-semibold text-primary">{{ $incident->equipment->equipment_code }}</div>
                            <div class="text-sm">{{ $incident->equipment->name }}</div>
                            <div class="text-xs text-muted">{{ $incident->equipment->category->name }}</div>
                        </td>
                        <td><span class="badge {{ $incident->type->badgeClass() }}">{{ $incident->type->label() }}</span></td>
                        <td>
                            <div>{{ str($incident->description)->limit(100) }}</div>
                            @if ($incident->resolution)
                                <div class="text-xs text-success mt-1">ผล: {{ str($incident->resolution)->limit(80) }}</div>
                            @endif
                        </td>
                        <td>{{ $incident->reporter->name }}</td>
                        <td><span class="badge {{ $state->badgeClass() }}">{{ $state->label() }}</span></td>
                        <td class="text-end">
                            @if (! $incident->resolved_at)
                                @can('resolve', $incident)
                                    <a href="{{ route('maintenance.resolve', $incident) }}" class="btn btn-sm btn-outline-primary">ปิดเหตุ</a>
                                @endcan
                            @else
                                <span class="text-xs text-muted">{{ $incident->resolver?->name ?: '—' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-5 text-center text-muted">ไม่พบเหตุขัดข้อง</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($incidents->hasPages())
        <div class="p-4 border-top">{{ $incidents->links() }}</div>
    @endif
</section>
@endsection
