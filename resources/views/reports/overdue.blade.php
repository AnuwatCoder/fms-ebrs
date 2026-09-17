@extends('layouts.app')

@section('title', 'รายงานเกินกำหนด')
@section('page-title', 'รายงานเกินกำหนด')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">รายงานเกินกำหนด</h1>
        <p class="text-muted mb-0">รายการที่เลยกำหนดคืนและยังอยู่ระหว่างดำเนินการ</p>
    </div>
    <div class="card px-4 py-3 d-flex flex-row align-items-center gap-3">
        <div class="icon-circle icon-circle-sm icon-circle-bg-danger-soft"><i data-lucide="clock-alert" class="lucide-sm text-danger"></i></div>
        <div><div class="text-xs text-slate-500">เกินกำหนดทั้งหมด</div><strong class="h5 mb-0 text-danger">{{ number_format($totalOverdue) }}</strong></div>
    </div>
</section>

<section class="card p-4">
    <form method="GET" action="{{ route('report.overdue') }}" class="row g-3 align-items-end">
        <div class="col-12 col-md-10">
            <label for="search" class="form-label">ค้นหา</label>
            <input id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="เลขที่คำขอหรือชื่อผู้ยืม">
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">ค้นหา</button>
            <a href="{{ route('report.overdue') }}" class="btn btn-outline-secondary"><i data-lucide="rotate-ccw" class="lucide-sm"></i></a>
        </div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>เลขที่คำขอ</th><th>ผู้ยืม</th><th>กำหนดคืน</th><th>เกินกำหนด</th><th>จำนวน</th><th>สถานะ</th></tr></thead>
            <tbody>
                @forelse ($borrowRequests as $borrowRequest)
                    <tr>
                        <td class="fw-semibold text-primary">{{ $borrowRequest->request_no }}</td>
                        <td><div>{{ $borrowRequest->borrower->name }}</div><div class="text-xs text-slate-500">{{ $borrowRequest->borrower->email }}</div></td>
                        <td>{{ $borrowRequest->expected_return_date->format('d/m/Y') }}</td>
                        <td class="fw-bold text-danger">{{ $borrowRequest->expected_return_date->diffInDays(today()) }} วัน</td>
                        <td>{{ $borrowRequest->items_count }} รายการ</td>
                        <td><span class="badge {{ $borrowRequest->status->badgeClass() }}">{{ $borrowRequest->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-5 text-center text-muted">ไม่มีรายการเกินกำหนด</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($borrowRequests->hasPages())<div class="p-4 border-top">{{ $borrowRequests->links() }}</div>@endif
</section>
@endsection
