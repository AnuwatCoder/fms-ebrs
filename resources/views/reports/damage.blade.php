@extends('layouts.app')

@section('title', 'รายงานความเสียหาย')
@section('page-title', 'รายงานความเสียหาย')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h3 fw-bold text-slate-800 mb-1">รายงานความเสียหาย</h1>
        <p class="text-muted mb-0">เหตุขัดข้อง ความเสียหาย และอุปกรณ์สูญหายจากการใช้งาน</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge badge-soft-danger px-3 py-2">ยังไม่ปิด {{ number_format($openCount) }}</span>
        <span class="badge badge-soft-success px-3 py-2">แก้ไขแล้ว {{ number_format($resolvedCount) }}</span>
    </div>
</section>

<section class="card p-4">
    <form method="GET" action="{{ route('report.damage') }}" class="row g-3 align-items-end">
        <div class="col-12 col-md-5">
            <label for="type" class="form-label">ประเภท</label>
            <select id="type" name="type" class="form-select">
                <option value="">ทุกประเภท</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-5">
            <label for="resolution" class="form-label">การดำเนินการ</label>
            <select id="resolution" name="resolution" class="form-select">
                <option value="">ทั้งหมด</option>
                @foreach ($states as $state)
                    <option value="{{ $state->value }}" @selected(request('resolution') === $state->value)>{{ $state->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">แสดงผล</button>
            <a href="{{ route('report.damage') }}" class="btn btn-outline-secondary"><i data-lucide="rotate-ccw" class="lucide-sm"></i></a>
        </div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>วันที่รายงาน</th><th>อุปกรณ์</th><th>ประเภท</th><th>รายละเอียด</th><th>คำขอ</th><th>ผู้รายงาน</th><th>สถานะ</th></tr></thead>
            <tbody>
                @forelse ($incidents as $incident)
                    <tr>
                        <td>{{ $incident->reported_at->format('d/m/Y H:i') }}</td>
                        <td><div class="fw-semibold text-primary">{{ $incident->equipment->equipment_code }}</div><div class="text-sm">{{ $incident->equipment->name }}</div></td>
                        <td><span class="badge {{ $incident->type->badgeClass() }}">{{ $incident->type->label() }}</span></td>
                        <td>{{ \Illuminate\Support\Str::limit($incident->description, 80) }}</td>
                        <td>{{ $incident->borrowRequest?->request_no ?: '—' }}</td>
                        <td>{{ $incident->reporter->name }}</td>
                        <td>
                            @if ($incident->resolved_at)
                                <span class="badge badge-soft-success">แก้ไขแล้ว</span>
                            @else
                                <span class="badge badge-soft-warning">รอดำเนินการ</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-5 text-center text-muted">ไม่พบรายงานความเสียหาย</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($incidents->hasPages())<div class="p-4 border-top">{{ $incidents->links() }}</div>@endif
</section>
@endsection
