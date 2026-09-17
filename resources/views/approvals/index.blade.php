@extends('layouts.app')

@section('title', 'คำขอรออนุมัติ')
@section('page-title', 'คำขอรออนุมัติ')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">คำขอรออนุมัติ</h1>
    <p class="text-muted mb-0">ตรวจสอบรายละเอียดและความพร้อมของอุปกรณ์ก่อนตัดสินใจ</p>
</section>

<section class="card overflow-hidden">
    <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
        <div>
            <h2 class="h5 fw-bold text-slate-800 mb-1">รายการรอพิจารณา</h2>
            <p class="text-xs text-slate-500 mb-0">พบ {{ number_format($borrowRequests->total()) }} คำขอ</p>
        </div>
        <i data-lucide="badge-check" class="lucide-md text-primary"></i>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0" data-operation-table="approvals">
            <thead>
                <tr>
                    <th>เลขที่คำขอ</th>
                    <th>ผู้ยืม</th>
                    <th>วัตถุประสงค์</th>
                    <th>ช่วงวันที่ยืม</th>
                    <th class="text-center">อุปกรณ์</th>
                    <th>ส่งคำขอเมื่อ</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($borrowRequests as $borrowRequest)
                    <tr>
                        <td>
                            <div class="fw-semibold text-primary">{{ $borrowRequest->request_no }}</div>
                            <span class="badge {{ $borrowRequest->status->badgeClass() }}">{{ $borrowRequest->status->label() }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-slate-800">{{ $borrowRequest->borrower->name }}</div>
                            <div class="text-xs text-muted">{{ $borrowRequest->borrower->email ?: '—' }}</div>
                        </td>
                        <td>{{ str($borrowRequest->purpose)->limit(60) }}</td>
                        <td>
                            <div>{{ $borrowRequest->borrow_date->format('d/m/Y') }}</div>
                            <div class="text-xs text-muted">ถึง {{ $borrowRequest->expected_return_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="text-center">{{ number_format($borrowRequest->items->count()) }}</td>
                        <td>{{ optional($borrowRequest->submitted_at)->format('d/m/Y H:i') ?: '—' }}</td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2"
                                data-bs-toggle="modal"
                                data-bs-target="#approval-modal-{{ $borrowRequest->id }}"
                                data-action-modal="approval"
                            >
                                <i data-lucide="clipboard-check" class="lucide-sm"></i>
                                {{ auth()->user()->canAny(['approval.approve', 'approval.reject']) ? 'พิจารณา' : 'ดูรายละเอียด' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-5 text-center">
                            <div class="icon-circle icon-circle-xl icon-circle-bg-success-soft mx-auto mb-3">
                                <i data-lucide="badge-check" class="lucide-lg text-success"></i>
                            </div>
                            <h3 class="h6 fw-semibold mb-1">ไม่มีคำขอที่รออนุมัติ</h3>
                            <p class="text-muted small mb-0">ดำเนินการครบทั้งหมดแล้ว</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($borrowRequests->hasPages())
        <div class="p-4 border-top">{{ $borrowRequests->links() }}</div>
    @endif
</section>

@foreach ($borrowRequests as $borrowRequest)
    <div
        class="modal fade"
        id="approval-modal-{{ $borrowRequest->id }}"
        tabindex="-1"
        aria-labelledby="approval-modal-label-{{ $borrowRequest->id }}"
        aria-hidden="true"
        data-operation-modal="approval"
    >
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 fw-bold" id="approval-modal-label-{{ $borrowRequest->id }}">
                            พิจารณาคำขอ {{ $borrowRequest->request_no }}
                        </h2>
                        <div class="text-sm text-muted">{{ $borrowRequest->borrower->name }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>

                <form method="POST" action="{{ route('approval.update', $borrowRequest) }}" data-operation-form="approval">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="operation_request_id" value="{{ $borrowRequest->id }}">

                    <div class="modal-body">
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-lg-8">
                                <div class="text-xs text-muted mb-1">วัตถุประสงค์</div>
                                <div>{{ $borrowRequest->purpose }}</div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="text-xs text-muted mb-1">สถานที่ใช้งาน</div>
                                <div>{{ $borrowRequest->usage_location ?: 'ไม่ระบุ' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-xs text-muted mb-1">ระยะเวลายืม</div>
                                <div>{{ $borrowRequest->borrow_date->format('d/m/Y') }} – {{ $borrowRequest->expected_return_date->format('d/m/Y') }}</div>
                            </div>
                        </div>

                        <div class="table-responsive border rounded mb-4">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>รหัส</th>
                                        <th>อุปกรณ์</th>
                                        <th>หมวดหมู่</th>
                                        <th>ความพร้อม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($borrowRequest->items as $item)
                                        <tr>
                                            <td class="fw-semibold text-primary">{{ $item->equipment->equipment_code }}</td>
                                            <td>{{ $item->equipment->name }}</td>
                                            <td>{{ $item->equipment->category->name }}</td>
                                            <td>
                                                <span class="badge {{ $item->equipment->status->badgeClass() }}">
                                                    {{ $item->equipment->status->label() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if (auth()->user()->canAny(['approval.approve', 'approval.reject']))
                            <label for="comment-{{ $borrowRequest->id }}" class="form-label">ความเห็นของผู้อนุมัติ</label>
                            <textarea
                                id="comment-{{ $borrowRequest->id }}"
                                name="comment"
                                rows="3"
                                maxlength="2000"
                                class="form-control @error('comment') is-invalid @enderror"
                                placeholder="ระบุเหตุผล โดยจำเป็นเมื่อไม่อนุมัติ"
                            >{{ old('operation_request_id') == $borrowRequest->id ? old('comment') : '' }}</textarea>
                            @if (old('operation_request_id') == $borrowRequest->id)
                                @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @endif
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        @can('approval.reject')
                            <button type="submit" name="action" value="{{ $rejectAction->value }}" class="btn btn-outline-danger d-inline-flex align-items-center gap-2">
                                <i data-lucide="x-circle" class="lucide-sm"></i>
                                ไม่อนุมัติ
                            </button>
                        @endcan
                        @can('approval.approve')
                            <button type="submit" name="action" value="{{ $approveAction->value }}" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i data-lucide="check-circle" class="lucide-sm"></i>
                                อนุมัติและจองอุปกรณ์
                            </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@if ($errors->any() && old('operation_request_id'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById(@json('approval-modal-'.old('operation_request_id')));
                if (modal && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endpush
@endif
