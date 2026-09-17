@extends('layouts.app')

@section('title', $mine ? 'คำขอยืมของฉัน' : 'คำขอยืมทั้งหมด')
@section('page-title', $mine ? 'คำขอยืมของฉัน' : 'คำขอยืมทั้งหมด')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">{{ $mine ? 'คำขอยืมของฉัน' : 'คำขอยืมทั้งหมด' }}</h1>
        <p class="text-muted mb-0">ติดตามสถานะและช่วงเวลาของคำขอยืมอุปกรณ์</p>
    </div>
    @can('borrow.create')
        <a href="{{ route('borrow.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i data-lucide="circle-plus" class="lucide-sm"></i>
            สร้างคำขอยืม
        </a>
    @endcan
</section>

<section class="card p-4">
    <form method="GET" action="{{ $mine ? route('borrow.mine') : route('borrow.index') }}" class="row g-3 align-items-end">
        <div class="col-12 col-lg-7">
            <label for="search" class="form-label">ค้นหา</label>
            <input id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="เลขที่คำขอ วัตถุประสงค์ หรือชื่อผู้ยืม">
        </div>
        <div class="col-12 col-md-7 col-lg-3">
            <label for="status" class="form-label">สถานะ</label>
            <select id="status" name="status" class="form-select">
                <option value="">ทุกสถานะ</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-5 col-lg-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">ค้นหา</button>
            <a class="btn btn-outline-secondary" href="{{ $mine ? route('borrow.mine') : route('borrow.index') }}" aria-label="ล้างตัวกรอง">
                <i data-lucide="rotate-ccw" class="lucide-sm"></i>
            </a>
        </div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
        <div>
            <h2 class="h5 fw-bold text-slate-800 mb-1">รายการคำขอ</h2>
            <p class="text-xs text-slate-500 mb-0">พบ {{ number_format($borrowRequests->total()) }} รายการ</p>
        </div>
        <i data-lucide="clipboard-list" class="lucide-md text-primary"></i>
    </div>

    @if ($borrowRequests->isEmpty())
        <div class="p-5 text-center">
            <div class="icon-circle icon-circle-xl icon-circle-bg-info-soft mx-auto mb-3">
                <i data-lucide="inbox" class="lucide-lg text-info"></i>
            </div>
            <h3 class="h6 fw-semibold mb-1">ไม่พบคำขอยืม</h3>
            <p class="text-muted small mb-0">คำขอใหม่จะแสดงในหน้านี้หลังจากส่งคำขอแล้ว</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>เลขที่คำขอ</th>
                        @unless ($mine)<th>ผู้ยืม</th>@endunless
                        <th>วัตถุประสงค์</th>
                        <th>ช่วงวันที่</th>
                        <th>อุปกรณ์</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($borrowRequests as $borrowRequest)
                        <tr>
                            <td>
                                <div class="fw-semibold text-primary">{{ $borrowRequest->request_no }}</div>
                                <div class="text-xs text-slate-500">{{ optional($borrowRequest->submitted_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            @unless ($mine)<td>{{ $borrowRequest->borrower->name }}</td>@endunless
                            <td title="{{ $borrowRequest->purpose }}">{{ \Illuminate\Support\Str::limit($borrowRequest->purpose, 55) }}</td>
                            <td>
                                <div>{{ $borrowRequest->borrow_date->format('d/m/Y') }}</div>
                                <div class="text-xs text-slate-500">ถึง {{ $borrowRequest->expected_return_date->format('d/m/Y') }}</div>
                            </td>
                            <td>{{ number_format($borrowRequest->items_count) }} รายการ</td>
                            <td><span class="badge {{ $borrowRequest->status->badgeClass() }}">{{ $borrowRequest->status->label() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($borrowRequests->hasPages())
            <div class="p-4 border-top">{{ $borrowRequests->links() }}</div>
        @endif
    @endif
</section>
@endsection
