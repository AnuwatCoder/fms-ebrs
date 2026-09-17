@extends('layouts.app')

@section('title', 'รายงานอุปกรณ์')
@section('page-title', 'รายงานอุปกรณ์')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">รายงานอุปกรณ์</h1>
    <p class="text-muted mb-0">สรุปจำนวนและสถานะปัจจุบันของอุปกรณ์ทั้งหมด</p>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    @foreach ($statuses as $status)
        <article class="card p-4">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-sm text-slate-500 mb-1">{{ $status->label() }}</div>
                    <div class="h4 fw-bold text-slate-800 mb-0">{{ number_format((int) ($statusCounts[$status->value] ?? 0)) }}</div>
                </div>
                <span class="badge {{ $status->badgeClass() }}">{{ $status->value }}</span>
            </div>
        </article>
    @endforeach
</section>

<section class="card p-4">
    <form method="GET" action="{{ route('report.equipment') }}" class="row g-3 align-items-end">
        <div class="col-12 col-lg-4">
            <label for="search" class="form-label">ค้นหา</label>
            <input id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="รหัส ชื่อ หรือเลขครุภัณฑ์">
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <label for="category" class="form-label">หมวดหมู่</label>
            <select id="category" name="category" class="form-select">
                <option value="">ทุกหมวดหมู่</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <label for="status" class="form-label">สถานะ</label>
            <select id="status" name="status" class="form-select">
                <option value="">ทุกสถานะ</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">แสดงผล</button>
            <a href="{{ route('report.equipment') }}" class="btn btn-outline-secondary"><i data-lucide="rotate-ccw" class="lucide-sm"></i></a>
        </div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>รหัส</th><th>อุปกรณ์</th><th>หมวดหมู่</th><th>สถานที่</th><th>สถานะ</th><th>เปิดใช้</th></tr></thead>
            <tbody>
                @forelse ($equipment as $item)
                    <tr>
                        <td class="fw-semibold text-primary">{{ $item->equipment_code }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category->name }}</td>
                        <td>{{ $item->location ?: '—' }}</td>
                        <td><span class="badge {{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                        <td>{{ $item->active ? 'ใช่' : 'ไม่' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-5 text-center text-muted">ไม่พบข้อมูลตามตัวกรอง</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($equipment->hasPages())<div class="p-4 border-top">{{ $equipment->links() }}</div>@endif
</section>
@endsection
