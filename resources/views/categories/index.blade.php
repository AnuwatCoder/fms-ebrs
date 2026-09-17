@extends('layouts.app')

@section('title', 'หมวดหมู่อุปกรณ์')
@section('page-title', 'หมวดหมู่อุปกรณ์')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">หมวดหมู่อุปกรณ์</h1>
        <p class="text-muted mb-0">จัดกลุ่มอุปกรณ์และควบคุมหมวดหมู่ที่เปิดให้เลือกใช้งาน</p>
    </div>
    @can('category.create')
        <a href="{{ route('category.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i data-lucide="plus" class="lucide-sm"></i>
            เพิ่มหมวดหมู่
        </a>
    @endcan
</section>

<section class="card p-4">
    <form method="GET" action="{{ route('category.index') }}" class="row g-3 align-items-end">
        <div class="col-12 col-md-7">
            <label for="search" class="form-label">ค้นหา</label>
            <input id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="ชื่อ รหัส หรือรายละเอียดหมวดหมู่">
        </div>
        <div class="col-12 col-md-3">
            <label for="active" class="form-label">สถานะ</label>
            <select id="active" name="active" class="form-select">
                <option value="">ทั้งหมด</option>
                <option value="1" @selected(request('active') === '1')>เปิดใช้งาน</option>
                <option value="0" @selected(request('active') === '0')>ปิดใช้งาน</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">ค้นหา</button>
            <a class="btn btn-outline-secondary" href="{{ route('category.index') }}" aria-label="ล้างตัวกรอง">
                <i data-lucide="rotate-ccw" class="lucide-sm"></i>
            </a>
        </div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
        <div>
            <h2 class="h5 fw-bold text-slate-800 mb-1">รายการหมวดหมู่</h2>
            <p class="text-xs text-slate-500 mb-0">พบ {{ number_format($categories->total()) }} รายการ</p>
        </div>
        <i data-lucide="tags" class="lucide-md text-primary"></i>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อหมวดหมู่</th>
                    <th>รายละเอียด</th>
                    <th class="text-center">อุปกรณ์</th>
                    <th>สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td class="fw-semibold text-primary">{{ $category->code }}</td>
                        <td class="fw-semibold text-slate-800">{{ $category->name }}</td>
                        <td>{{ str($category->description ?: '—')->limit(80) }}</td>
                        <td class="text-center">{{ number_format($category->equipment_count) }}</td>
                        <td>
                            <span class="badge {{ $category->active ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                {{ $category->active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                @can('update', $category)
                                    <a href="{{ route('category.edit', $category) }}" class="btn btn-sm btn-outline-primary" aria-label="แก้ไข {{ $category->name }}">
                                        <i data-lucide="pencil" class="lucide-sm"></i>
                                    </a>
                                @endcan
                                @can('delete', $category)
                                    <form method="POST" action="{{ route('category.destroy', $category) }}" onsubmit="return confirm('ยืนยันการลบหมวดหมู่นี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="ลบ {{ $category->name }}">
                                            <i data-lucide="trash-2" class="lucide-sm"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-5 text-center text-muted">ไม่พบหมวดหมู่อุปกรณ์</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($categories->hasPages())
        <div class="p-4 border-top">{{ $categories->links() }}</div>
    @endif
</section>
@endsection
