@extends('layouts.app')

@section('title', 'บันทึกกิจกรรม')
@section('page-title', 'บันทึกกิจกรรม')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">บันทึกกิจกรรม</h1>
    <p class="text-muted mb-0">ประวัติการเปลี่ยนแปลงข้อมูลสำคัญแบบอ่านอย่างเดียว</p>
</section>

<section class="card p-4">
    <form method="GET" action="{{ route('audit.index') }}" class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
            <label for="event" class="form-label">เหตุการณ์</label>
            <select id="event" name="event" class="form-select"><option value="">ทั้งหมด</option>@foreach ($events as $event)<option value="{{ $event }}" @selected(request('event') === $event)>{{ $event }}</option>@endforeach</select>
        </div>
        <div class="col-12 col-md-3">
            <label for="causer" class="form-label">ผู้ดำเนินการ</label>
            <select id="causer" name="causer" class="form-select"><option value="">ทั้งหมด</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected((string) request('causer') === (string) $user->id)>{{ $user->name }}</option>@endforeach</select>
        </div>
        <div class="col-6 col-md-2"><label for="from" class="form-label">ตั้งแต่</label><input id="from" type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
        <div class="col-6 col-md-2"><label for="to" class="form-label">ถึง</label><input id="to" type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
        <div class="col-12 col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1">แสดงผล</button><a href="{{ route('audit.index') }}" class="btn btn-outline-secondary"><i data-lucide="rotate-ccw" class="lucide-sm"></i></a></div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>วันเวลา</th><th>เหตุการณ์</th><th>ผู้ดำเนินการ</th><th>ข้อมูลที่เกี่ยวข้อง</th><th>IP</th><th>รายละเอียด</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td><code>{{ $log->event }}</code></td>
                        <td>{{ $log->causer?->name ?: 'ระบบ' }}</td>
                        <td><div class="text-xs">{{ class_basename($log->subject_type ?: '—') }}</div><div class="text-slate-500">#{{ $log->subject_id ?: '—' }}</div></td>
                        <td>{{ $log->ip_address ?: '—' }}</td>
                        <td>
                            @if ($log->old_values || $log->new_values)
                                <details><summary class="text-primary" style="cursor:pointer">ดูการเปลี่ยนแปลง</summary><pre class="small mt-2 mb-0">{{ json_encode(['เดิม' => $log->old_values, 'ใหม่' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-5 text-center text-muted">ยังไม่มีบันทึกกิจกรรม</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())<div class="p-4 border-top">{{ $logs->links() }}</div>@endif
</section>
@endsection
