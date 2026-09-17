@extends('layouts.app')

@section('title', 'จ่ายอุปกรณ์')
@section('page-title', 'จ่ายอุปกรณ์')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">จ่ายอุปกรณ์</h1>
    <p class="text-muted mb-0">เตรียมคำขอที่อนุมัติแล้วและบันทึกสภาพอุปกรณ์ก่อนส่งมอบ</p>
</section>

<section class="card overflow-hidden">
    <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
        <div>
            <h2 class="h5 fw-bold text-slate-800 mb-1">รายการรอจ่าย</h2>
            <p class="text-xs text-slate-500 mb-0">พบ {{ number_format($borrowRequests->total()) }} คำขอ</p>
        </div>
        <i data-lucide="scan-line" class="lucide-md text-primary"></i>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0" data-operation-table="checkouts">
            <thead>
                <tr>
                    <th>เลขที่คำขอ</th>
                    <th>ผู้ยืม</th>
                    <th>วันที่ยืม</th>
                    <th class="text-center">อุปกรณ์</th>
                    <th>สถานะ</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($borrowRequests as $borrowRequest)
                    <tr>
                        <td class="fw-semibold text-primary">{{ $borrowRequest->request_no }}</td>
                        <td>
                            <div class="fw-semibold text-slate-800">{{ $borrowRequest->borrower->name }}</div>
                            <div class="text-xs text-muted">{{ $borrowRequest->borrower->email ?: '—' }}</div>
                        </td>
                        <td>{{ $borrowRequest->borrow_date->format('d/m/Y') }}</td>
                        <td class="text-center">{{ number_format($borrowRequest->items->count()) }}</td>
                        <td><span class="badge {{ $borrowRequest->status->badgeClass() }}">{{ $borrowRequest->status->label() }}</span></td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2"
                                data-bs-toggle="modal"
                                data-bs-target="#checkout-modal-{{ $borrowRequest->id }}"
                                data-action-modal="checkout"
                            >
                                <i data-lucide="{{ $borrowRequest->status === $approvedStatus ? 'package-check' : 'hand-platter' }}" class="lucide-sm"></i>
                                {{ $borrowRequest->status === $approvedStatus ? 'เตรียมพร้อมรับ' : 'จ่ายอุปกรณ์' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-5 text-center">
                            <div class="icon-circle icon-circle-xl icon-circle-bg-success-soft mx-auto mb-3">
                                <i data-lucide="scan-line" class="lucide-lg text-success"></i>
                            </div>
                            <h3 class="h6 fw-semibold mb-1">ไม่มีรายการรอจ่าย</h3>
                            <p class="text-muted small mb-0">คำขอที่อนุมัติแล้วจะแสดงในหน้านี้</p>
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
    @php($isPreparing = $borrowRequest->status === $approvedStatus)
    <div
        class="modal fade"
        id="checkout-modal-{{ $borrowRequest->id }}"
        tabindex="-1"
        aria-labelledby="checkout-modal-label-{{ $borrowRequest->id }}"
        aria-hidden="true"
        data-operation-modal="checkout"
    >
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 fw-bold" id="checkout-modal-label-{{ $borrowRequest->id }}">
                            {{ $isPreparing ? 'เตรียมอุปกรณ์ให้พร้อมรับ' : 'ยืนยันการจ่ายอุปกรณ์' }}
                        </h2>
                        <div class="text-sm text-muted">{{ $borrowRequest->request_no }} · {{ $borrowRequest->borrower->name }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>

                <form method="POST" action="{{ route('checkout.update', $borrowRequest) }}" data-operation-form="checkout">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="operation_request_id" value="{{ $borrowRequest->id }}">
                    <input type="hidden" name="action" value="{{ $isPreparing ? $readyAction : $checkoutAction }}">

                    <div class="modal-body">
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="text-xs text-muted mb-1">วันที่ยืม</div>
                                <div>{{ $borrowRequest->borrow_date->format('d/m/Y') }}</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="text-xs text-muted mb-1">กำหนดคืน</div>
                                <div>{{ $borrowRequest->expected_return_date->format('d/m/Y') }}</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="text-xs text-muted mb-1">สถานะ</div>
                                <span class="badge {{ $borrowRequest->status->badgeClass() }}">{{ $borrowRequest->status->label() }}</span>
                            </div>
                        </div>

                        <div class="table-responsive border rounded">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>รหัส</th>
                                        <th>อุปกรณ์</th>
                                        <th>หมวดหมู่</th>
                                        <th>สถานที่จัดเก็บ</th>
                                        @if (! $isPreparing)<th style="min-width: 280px">สภาพก่อนจ่าย</th>@endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($borrowRequest->items as $item)
                                        <tr>
                                            <td class="fw-semibold text-primary">{{ $item->equipment->equipment_code }}</td>
                                            <td>{{ $item->equipment->name }}</td>
                                            <td>{{ $item->equipment->category->name }}</td>
                                            <td>{{ $item->equipment->location ?: '—' }}</td>
                                            @if (! $isPreparing)
                                                <td>
                                                    <input
                                                        name="conditions[{{ $item->id }}]"
                                                        class="form-control @error('conditions.'.$item->id) is-invalid @enderror"
                                                        value="{{ old('operation_request_id') == $borrowRequest->id ? old('conditions.'.$item->id, 'สภาพปกติ พร้อมใช้งาน') : 'สภาพปกติ พร้อมใช้งาน' }}"
                                                        maxlength="2000"
                                                        required
                                                    >
                                                    @if (old('operation_request_id') == $borrowRequest->id)
                                                        @error('conditions.'.$item->id)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if (! $isPreparing)
                            <div class="mt-4">
                                <label for="checkout-note-{{ $borrowRequest->id }}" class="form-label">หมายเหตุการจ่าย</label>
                                <textarea id="checkout-note-{{ $borrowRequest->id }}" name="note" rows="3" maxlength="2000" class="form-control">{{ old('operation_request_id') == $borrowRequest->id ? old('note') : '' }}</textarea>
                            </div>
                        @else
                            <div class="alert alert-info d-flex gap-2 mt-4 mb-0" role="status">
                                <i data-lucide="info" class="lucide-sm flex-shrink-0"></i>
                                <div>ยืนยันว่าอุปกรณ์ทุกรายการถูกจัดเตรียมและพร้อมให้ผู้ยืมมารับ</div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        @can('checkout', $borrowRequest)
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i data-lucide="{{ $isPreparing ? 'package-check' : 'hand-platter' }}" class="lucide-sm"></i>
                                {{ $isPreparing ? 'ยืนยันว่าพร้อมรับ' : 'ยืนยันการจ่ายอุปกรณ์' }}
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
                const modal = document.getElementById(@json('checkout-modal-'.old('operation_request_id')));
                if (modal && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endpush
@endif
