@extends('layouts.app')

@section('title', $borrowRequest->request_no)
@section('page-title', 'รายละเอียดคำขอยืม')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h1 class="h3 fw-bold text-slate-800 mb-0">{{ $borrowRequest->request_no }}</h1>
            <span class="badge {{ $borrowRequest->status->badgeClass() }}">{{ $borrowRequest->status->label() }}</span>
        </div>
        <p class="text-muted mb-0">ผู้ยืม {{ $borrowRequest->borrower->name }} · {{ $borrowRequest->borrow_date->format('d/m/Y') }} ถึง {{ $borrowRequest->expected_return_date->format('d/m/Y') }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ auth()->user()->can('borrow.view') ? route('borrow.index') : route('borrow.mine') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i data-lucide="arrow-left" class="lucide-sm"></i>
            กลับไปรายการคำขอ
        </a>
        @can('update', $borrowRequest)
            <a href="{{ route('borrow.edit', $borrowRequest) }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i data-lucide="pencil" class="lucide-sm"></i>
                แก้ไขฉบับร่าง
            </a>
        @endcan
    </div>
</section>

<section class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="d-grid gap-4">
            <article class="card p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h2 class="h5 fw-bold text-slate-800 mb-0">ข้อมูลคำขอ</h2>
                    <i data-lucide="clipboard-list" class="lucide-md text-primary"></i>
                </div>
                <dl class="row g-4 mb-0">
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">ผู้ยืม</dt>
                        <dd class="mb-0">{{ $borrowRequest->borrower->name }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">อีเมล</dt>
                        <dd class="mb-0">{{ $borrowRequest->borrower->email ?: '—' }}</dd>
                    </div>
                    <div class="col-12">
                        <dt class="text-xs text-slate-500 mb-1">วัตถุประสงค์</dt>
                        <dd class="mb-0">{{ $borrowRequest->purpose }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">สถานที่ใช้งาน</dt>
                        <dd class="mb-0">{{ $borrowRequest->usage_location ?: '—' }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-xs text-slate-500 mb-1">หมายเหตุ</dt>
                        <dd class="mb-0">{{ $borrowRequest->note ?: '—' }}</dd>
                    </div>
                </dl>
            </article>

            <article class="card overflow-hidden">
                <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
                    <div>
                        <h2 class="h5 fw-bold text-slate-800 mb-1">อุปกรณ์ทั้งหมด</h2>
                        <p class="text-xs text-slate-500 mb-0">{{ number_format($borrowRequest->items->count()) }} รายการ</p>
                    </div>
                    <i data-lucide="boxes" class="lucide-md text-primary"></i>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>อุปกรณ์</th>
                                <th>สถานะ</th>
                                <th>การจ่าย</th>
                                <th>การคืน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($borrowRequest->items as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('equipment.show', $item->equipment) }}" class="fw-semibold text-primary text-decoration-none">{{ $item->equipment->equipment_code }}</a>
                                        <div>{{ $item->equipment->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->equipment->category->name }}</div>
                                    </td>
                                    <td><span class="badge {{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                                    <td>
                                        @if ($item->checkout)
                                            <div>{{ $item->checkout->checked_out_at->format('d/m/Y H:i') }}</div>
                                            <div class="text-xs text-slate-500">{{ $item->checkout->staff->name }}</div>
                                            <div class="text-xs mt-1">{{ $item->checkout->condition_before }}</div>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->equipmentReturn)
                                            <div>{{ $item->equipmentReturn->returned_at->format('d/m/Y H:i') }}</div>
                                            <div class="text-xs text-slate-500">{{ $item->equipmentReturn->receiver->name }}</div>
                                            <span class="badge {{ $item->equipmentReturn->return_status->badgeClass() }} mt-1">{{ $item->equipmentReturn->return_status->label() }}</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>

            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <article class="card h-100 overflow-hidden">
                        <div class="p-4 border-bottom">
                            <h2 class="h5 fw-bold text-slate-800 mb-0">การอนุมัติและความเห็น</h2>
                        </div>
                        @forelse ($borrowRequest->approvals as $approval)
                            <div class="p-4 border-bottom">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="badge {{ $approval->action === \App\Enums\ApprovalAction::Approved ? 'badge-soft-success' : 'badge-soft-danger' }}">{{ $approval->action->label() }}</span>
                                    <span class="text-xs text-slate-500">{{ $approval->acted_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="fw-semibold">{{ $approval->approver->name }}</div>
                                <p class="text-sm text-slate-600 mb-0 mt-1">{{ $approval->comment ?: 'ไม่มีความเห็นเพิ่มเติม' }}</p>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">ยังไม่มีการพิจารณาคำขอ</div>
                        @endforelse
                    </article>
                </div>
                <div class="col-12 col-lg-6">
                    <article class="card h-100 overflow-hidden">
                        <div class="p-4 border-bottom">
                            <h2 class="h5 fw-bold text-slate-800 mb-0">เหตุขัดข้อง</h2>
                        </div>
                        @forelse ($borrowRequest->incidents as $incident)
                            <div class="p-4 border-bottom">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="badge {{ $incident->type->badgeClass() }}">{{ $incident->type->label() }}</span>
                                    <span class="badge {{ $incident->state()->badgeClass() }}">{{ $incident->state()->label() }}</span>
                                </div>
                                <div class="fw-semibold">{{ $incident->equipment->equipment_code }} · {{ $incident->equipment->name }}</div>
                                <p class="text-sm text-slate-600 mb-1 mt-1">{{ $incident->description }}</p>
                                @if ($incident->resolution)
                                    <p class="text-sm text-success mb-0">ผลดำเนินการ: {{ $incident->resolution }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">ไม่มีเหตุขัดข้องในคำขอนี้</div>
                        @endforelse
                    </article>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="d-grid gap-4">
            <article class="card overflow-hidden">
                <div class="p-4 border-bottom">
                    <h2 class="h5 fw-bold text-slate-800 mb-0">Timeline</h2>
                </div>
                <div class="p-4 d-grid gap-4">
                    @foreach ($timeline as $event)
                        <div class="d-flex gap-3">
                            <div class="icon-circle icon-circle-sm icon-circle-bg-{{ $event['tone'] }}-soft flex-shrink-0">
                                <i data-lucide="{{ $event['icon'] }}" class="lucide-sm text-{{ $event['tone'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-semibold text-slate-800">{{ $event['title'] }}</div>
                                <div class="text-sm text-slate-600">{{ $event['description'] }}</div>
                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $event['occurred_at']->format('d/m/Y H:i') }}
                                    @if ($event['actor']) · {{ $event['actor'] }} @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="card overflow-hidden">
                <div class="p-4 border-bottom">
                    <h2 class="h5 fw-bold text-slate-800 mb-0">Audit Log</h2>
                </div>
                @forelse ($auditLogs as $log)
                    <details class="p-3 border-bottom">
                        <summary class="d-flex align-items-center justify-content-between gap-2" style="cursor: pointer">
                            <span class="fw-semibold text-primary">{{ $log->event }}</span>
                            <span class="text-xs text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                        </summary>
                        <div class="text-xs text-slate-500 mt-2">โดย {{ $log->causer?->name ?? 'ระบบ' }}</div>
                        <pre class="small bg-light-subtle rounded p-2 mt-2 mb-0 text-wrap">{{ json_encode(['เดิม' => $log->old_values, 'ใหม่' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                @empty
                    <div class="p-4 text-center text-muted">ยังไม่มีบันทึกกิจกรรม</div>
                @endforelse
            </article>
        </div>
    </div>
</section>
@endsection
