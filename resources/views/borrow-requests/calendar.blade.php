@extends('layouts.app')

@section('title', 'ปฏิทินการจอง')
@section('page-title', 'ปฏิทินการจอง')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">ปฏิทินการจองและความพร้อมใช้งาน</h1>
        <p class="text-muted mb-0">ตรวจสอบจำนวนอุปกรณ์ที่พร้อมให้ยืมในแต่ละวันโดยไม่เปิดเผยข้อมูลผู้ยืม</p>
    </div>
    @can('borrow.create')
        <a href="{{ route('borrow.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i data-lucide="circle-plus" class="lucide-sm"></i>
            สร้างคำขอยืม
        </a>
    @endcan
</section>

<section class="row g-4">
    <div class="col-12 col-md-4">
        <article class="card p-4 h-100">
            <div class="text-xs text-slate-500 mb-2">อุปกรณ์ที่เปิดใช้งาน</div>
            <div class="h3 fw-bold text-slate-800 mb-0">{{ number_format($totalEquipment) }}</div>
        </article>
    </div>
    <div class="col-12 col-md-4">
        <article class="card p-4 h-100">
            <div class="text-xs text-slate-500 mb-2">พร้อมจัดตาราง</div>
            <div class="h3 fw-bold text-success mb-0">{{ number_format($schedulableEquipment) }}</div>
        </article>
    </div>
    <div class="col-12 col-md-4">
        <article class="card p-4 h-100">
            <div class="text-xs text-slate-500 mb-2">ไม่พร้อมจากสถานะปัจจุบัน</div>
            <div class="h3 fw-bold text-warning mb-0">{{ number_format(max(0, $totalEquipment - $schedulableEquipment)) }}</div>
        </article>
    </div>
</section>

<section class="card overflow-hidden">
    <div class="p-4 border-bottom">
        <form method="GET" action="{{ route('borrow.calendar') }}" class="row g-3 align-items-end">
            <input type="hidden" name="month" value="{{ $currentMonth }}">
            <div class="col-12 col-lg-8">
                <label for="category_id" class="form-label">หมวดหมู่อุปกรณ์</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">ทุกหมวดหมู่</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($selectedCategoryId === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">แสดงปฏิทิน</button>
                <a href="{{ route('borrow.calendar') }}" class="btn btn-outline-secondary" aria-label="ล้างตัวกรอง">
                    <i data-lucide="rotate-ccw" class="lucide-sm"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="d-flex align-items-center justify-content-between gap-3 p-4 border-bottom">
        <a href="{{ route('borrow.calendar', array_filter(['month' => $previousMonth, 'category_id' => $selectedCategoryId])) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
            <i data-lucide="chevron-left" class="lucide-sm"></i>
            เดือนก่อน
        </a>
        <h2 class="h5 fw-bold text-slate-800 mb-0">{{ $monthLabel }}</h2>
        <a href="{{ route('borrow.calendar', array_filter(['month' => $nextMonth, 'category_id' => $selectedCategoryId])) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
            เดือนถัดไป
            <i data-lucide="chevron-right" class="lucide-sm"></i>
        </a>
    </div>

    <div class="dashboard-calendar-scroll">
        <div class="dashboard-calendar">
            @foreach (['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'] as $weekday)
                <div class="dashboard-calendar-weekday">{{ $weekday }}</div>
            @endforeach

            @foreach ($calendarWeeks as $week)
                @foreach ($week as $day)
                    @if ($day === null)
                        <div class="dashboard-calendar-day dashboard-calendar-day-empty" aria-hidden="true"></div>
                    @else
                        <article class="dashboard-calendar-day {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['is_weekend'] ? 'is-weekend' : '' }}">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <strong class="dashboard-calendar-date">{{ $day['date']->day }}</strong>
                                @if ($day['is_today'])<span class="badge badge-soft-primary">วันนี้</span>@endif
                            </div>
                            <div class="mt-3">
                                <div class="fw-semibold {{ $day['available'] > 0 ? 'text-success' : 'text-danger' }}">
                                    ว่าง {{ number_format($day['available']) }}/{{ number_format($day['total']) }}
                                </div>
                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $day['requests'] > 0 ? 'มี '.$day['requests'].' คำขอ' : 'ยังไม่มีการจอง' }}
                                </div>
                            </div>
                        </article>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>
    <div class="p-3 border-top text-xs text-slate-500">
        จำนวนว่างคำนวณจากสถานะอุปกรณ์ปัจจุบันและคำขอที่รออนุมัติ อนุมัติแล้ว พร้อมรับ กำลังยืม หรือเกินกำหนด
    </div>
</section>
@endsection
