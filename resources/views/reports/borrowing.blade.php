@extends('layouts.app')

@section('title', 'รายงานการยืม')
@section('page-title', 'รายงานการยืม')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">รายงานการยืม</h1>
    <p class="text-muted mb-0">ตรวจสอบปริมาณคำขอและสถานะการยืมตามช่วงวันที่</p>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    @foreach ($statuses as $status)
        <article class="card p-4">
            <div class="text-sm text-slate-500 mb-1">{{ $status->label() }}</div>
            <div class="d-flex align-items-center justify-content-between">
                <strong class="h4 mb-0">{{ number_format((int) ($statusCounts[$status->value] ?? 0)) }}</strong>
                <span class="badge {{ $status->badgeClass() }}">{{ $status->value }}</span>
            </div>
        </article>
    @endforeach
</section>

<section class="card p-4">
    <form method="GET" action="{{ route('report.borrowing') }}" class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
            <label for="from" class="form-label">ตั้งแต่วันที่ยืม</label>
            <input id="from" type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-12 col-md-3">
            <label for="to" class="form-label">ถึงวันที่ยืม</label>
            <input id="to" type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-12 col-md-4">
            <label for="status" class="form-label">สถานะ</label>
            <select id="status" name="status" class="form-select">
                <option value="">ทุกสถานะ</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">แสดงผล</button>
            <a href="{{ route('report.borrowing') }}" class="btn btn-outline-secondary"><i data-lucide="rotate-ccw" class="lucide-sm"></i></a>
        </div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>เลขที่คำขอ</th><th>ผู้ยืม</th><th>ช่วงวันที่</th><th>จำนวน</th><th>สถานะ</th></tr></thead>
            <tbody>
                @forelse ($borrowRequests as $borrowRequest)
                    <tr>
                        <td class="fw-semibold text-primary">{{ $borrowRequest->request_no }}</td>
                        <td>{{ $borrowRequest->borrower->name }}</td>
                        <td>{{ $borrowRequest->borrow_date->format('d/m/Y') }} – {{ $borrowRequest->expected_return_date->format('d/m/Y') }}</td>
                        <td>{{ $borrowRequest->items_count }} รายการ</td>
                        <td><span class="badge {{ $borrowRequest->status->badgeClass() }}">{{ $borrowRequest->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-5 text-center text-muted">ไม่พบข้อมูลตามตัวกรอง</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($borrowRequests->hasPages())<div class="p-4 border-top">{{ $borrowRequests->links() }}</div>@endif
</section>
@endsection
