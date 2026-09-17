@extends('layouts.app')

@section('title', 'รับคืนอุปกรณ์')
@section('page-title', 'รับคืนอุปกรณ์')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">รับคืนอุปกรณ์</h1>
    <p class="text-muted mb-0">ตรวจสอบรายการยืมและบันทึกสภาพอุปกรณ์ผ่านหน้าต่างรับคืน</p>
</section>

<section class="card overflow-hidden">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-4 border-bottom">
        <div>
            <h2 class="h5 fw-bold mb-1">รายการรอรับคืน</h2>
            <p class="text-muted text-sm mb-0">ทั้งหมด {{ $borrowRequests->total() }} รายการ</p>
        </div>
        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
            หน้าที่ {{ $borrowRequests->currentPage() }} / {{ $borrowRequests->lastPage() }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0" data-operation-table="returns">
            <thead>
                <tr>
                    <th>เลขที่คำขอ</th>
                    <th>ผู้ยืม</th>
                    <th>กำหนดคืน</th>
                    <th class="text-center">จำนวน</th>
                    <th>สถานะ</th>
                    <th class="text-end">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($borrowRequests as $borrowRequest)
                    <tr>
                        <td>
                            <div class="fw-semibold text-primary">{{ $borrowRequest->request_no }}</div>
                            <div class="text-muted text-sm">{{ $borrowRequest->purpose }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $borrowRequest->borrower->name }}</div>
                            <div class="text-muted text-sm">{{ $borrowRequest->borrower->email }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $borrowRequest->expected_return_date->format('d/m/Y') }}</div>
                            @if ($borrowRequest->expected_return_date->isPast())
                                <div class="text-danger text-sm">
                                    เกินกำหนด {{ $borrowRequest->expected_return_date->diffInDays(today()) }} วัน
                                </div>
                            @else
                                <div class="text-muted text-sm">ยังไม่เกินกำหนด</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $borrowRequest->items->count() }}</td>
                        <td>
                            <span class="badge {{ $borrowRequest->status->badgeClass() }}">
                                {{ $borrowRequest->status->label() }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2"
                                data-bs-toggle="modal"
                                data-bs-target="#return-modal-{{ $borrowRequest->id }}"
                                data-action-modal="return"
                            >
                                <i data-lucide="package-check" class="lucide-sm"></i>
                                รับคืน
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-5 text-center">
                            <div class="icon-circle icon-circle-xl icon-circle-bg-success-soft mx-auto mb-3">
                                <i data-lucide="package-check" class="lucide-lg text-success"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-1">ไม่มีรายการรอรับคืน</h3>
                            <p class="text-muted mb-0">อุปกรณ์ที่กำลังถูกยืมจะแสดงในหน้านี้</p>
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
        id="return-modal-{{ $borrowRequest->id }}"
        tabindex="-1"
        aria-labelledby="return-modal-label-{{ $borrowRequest->id }}"
        aria-hidden="true"
        data-operation-modal="return"
    >
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form
                    method="POST"
                    action="{{ route('return.update', $borrowRequest) }}"
                    data-operation-form="return"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="operation_request_id" value="{{ $borrowRequest->id }}">

                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 mb-1" id="return-modal-label-{{ $borrowRequest->id }}">
                                รับคืน {{ $borrowRequest->request_no }}
                            </h2>
                            <div class="text-muted text-sm">
                                {{ $borrowRequest->borrower->name }} · กำหนดคืน {{ $borrowRequest->expected_return_date->format('d/m/Y') }}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="alert alert-warning rounded-0 border-0 border-bottom mb-0">
                            ตรวจสอบสภาพอุปกรณ์ทุกชิ้นก่อนยืนยัน ระบบจะปรับสถานะและบันทึกเหตุผิดปกติตามผลการตรวจรับ
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>อุปกรณ์</th>
                                        <th>สภาพก่อนจ่าย</th>
                                        <th style="min-width: 190px">ผลตรวจรับ</th>
                                        <th style="min-width: 250px">สภาพหลังคืน</th>
                                        <th style="min-width: 200px">หมายเหตุ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($borrowRequest->items as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-primary">{{ $item->equipment->equipment_code }}</div>
                                                <div class="text-sm">{{ $item->equipment->name }}</div>
                                            </td>
                                            <td>{{ $item->checkout?->condition_before ?: 'ไม่ระบุ' }}</td>
                                            <td>
                                                <select
                                                    name="items[{{ $item->id }}][return_status]"
                                                    class="form-select @error('items.'.$item->id.'.return_status') is-invalid @enderror"
                                                    required
                                                >
                                                    @foreach ($returnStatuses as $returnStatus)
                                                        <option
                                                            value="{{ $returnStatus->value }}"
                                                            @selected(old('items.'.$item->id.'.return_status', $returnStatuses[0]->value) === $returnStatus->value)
                                                        >
                                                            {{ $returnStatus->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('items.'.$item->id.'.return_status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input
                                                    name="items[{{ $item->id }}][condition_after]"
                                                    class="form-control @error('items.'.$item->id.'.condition_after') is-invalid @enderror"
                                                    value="{{ old('items.'.$item->id.'.condition_after', 'สภาพปกติ') }}"
                                                    required
                                                >
                                                @error('items.'.$item->id.'.condition_after')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input
                                                    name="items[{{ $item->id }}][note]"
                                                    class="form-control @error('items.'.$item->id.'.note') is-invalid @enderror"
                                                    value="{{ old('items.'.$item->id.'.note') }}"
                                                >
                                                @error('items.'.$item->id.'.note')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        @can('receiveReturn', $borrowRequest)
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                <i data-lucide="package-check" class="lucide-sm"></i>
                                ยืนยันการรับคืนทั้งหมด
                            </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
    @if ($errors->any() && old('operation_request_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modalElement = document.getElementById(@js('return-modal-'.old('operation_request_id')));

                if (modalElement) {
                    bootstrap.Modal.getOrCreateInstance(modalElement).show();
                }
            });
        </script>
    @endif
@endpush
