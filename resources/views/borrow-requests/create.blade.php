@extends('layouts.app')

@section('title', 'สร้างคำขอยืม')
@section('page-title', 'สร้างคำขอยืม')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">สร้างคำขอยืม</h1>
    <p class="text-muted mb-0">ระบุช่วงเวลาการใช้งานและเลือกอุปกรณ์ที่ต้องการ</p>
</section>

<form method="POST" action="{{ route('borrow.store') }}" class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    @csrf

    <section class="xl:col-span-1 card p-4 align-self-start">
        <div class="d-flex align-items-center gap-2 mb-4">
            <div class="icon-circle icon-circle-sm icon-circle-bg-primary-soft">
                <i data-lucide="clipboard-pen-line" class="lucide-sm text-primary"></i>
            </div>
            <h2 class="h5 fw-bold text-slate-800 mb-0">รายละเอียดคำขอ</h2>
        </div>

        <div class="mb-3">
            <label for="purpose" class="form-label">วัตถุประสงค์ <span class="text-danger">*</span></label>
            <textarea id="purpose" name="purpose" rows="4" class="form-control @error('purpose') is-invalid @enderror" required>{{ old('purpose') }}</textarea>
            @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="usage_location" class="form-label">สถานที่นำไปใช้งาน</label>
            <input id="usage_location" name="usage_location" class="form-control" value="{{ old('usage_location') }}">
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6 col-xl-12">
                <label for="borrow_date" class="form-label">วันที่ยืม <span class="text-danger">*</span></label>
                <input
                    id="borrow_date"
                    type="date"
                    name="borrow_date"
                    class="form-control @error('borrow_date') is-invalid @enderror"
                    min="{{ today()->format('Y-m-d') }}"
                    value="{{ old('borrow_date', today()->format('Y-m-d')) }}"
                    required
                >
            </div>
            <div class="col-12 col-md-6 col-xl-12">
                <label for="expected_return_date" class="form-label">กำหนดคืน <span class="text-danger">*</span></label>
                <input
                    id="expected_return_date"
                    type="date"
                    name="expected_return_date"
                    class="form-control @error('expected_return_date') is-invalid @enderror"
                    min="{{ today()->format('Y-m-d') }}"
                    value="{{ old('expected_return_date', today()->addDays($defaultLoanDays)->format('Y-m-d')) }}"
                    required
                >
            </div>
        </div>

        <div class="mb-4">
            <label for="note" class="form-label">หมายเหตุ</label>
            <textarea id="note" name="note" rows="3" class="form-control">{{ old('note') }}</textarea>
        </div>

        <div id="borrowing-terms" class="border rounded-3 overflow-hidden mb-4 @error('accept_terms') border-danger @enderror">
            <div class="d-flex align-items-center gap-2 px-3 py-2 bg-light-subtle border-bottom">
                <i data-lucide="file-check-2" class="lucide-sm text-primary"></i>
                <div>
                    <div class="fw-semibold text-slate-800">ข้อตกลงและเงื่อนไขการยืม</div>
                    <div class="text-muted text-xs">ฉบับวันที่ {{ $borrowingTerms['version'] }}</div>
                </div>
            </div>
            <div class="p-3">
                <ol class="ps-3 mb-3 text-sm text-slate-600 d-grid gap-2">
                    @foreach ($borrowingTerms['items'] as $term)
                        <li>{{ $term }}</li>
                    @endforeach
                </ol>
                <div class="form-check">
                    <input
                        id="accept_terms"
                        type="checkbox"
                        name="accept_terms"
                        value="1"
                        class="form-check-input @error('accept_terms') is-invalid @enderror"
                        aria-describedby="borrowing-terms"
                        data-borrow-terms
                        required
                        @checked(old('accept_terms'))
                    >
                    <label for="accept_terms" class="form-check-label fw-semibold">
                        ข้าพเจ้าได้อ่านและยอมรับข้อตกลงและเงื่อนไขทั้งหมด
                    </label>
                    @error('accept_terms')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <button
            type="submit"
            class="btn btn-primary d-flex align-items-center justify-content-center gap-2"
            data-borrow-submit
            data-has-equipment="{{ $equipment->isNotEmpty() ? '1' : '0' }}"
            @disabled($equipment->isEmpty() || ! old('accept_terms'))
        >
            <i data-lucide="send" class="lucide-sm"></i>
            ส่งคำขออนุมัติ
        </button>
    </section>

    <section class="xl:col-span-2 card overflow-hidden">
        <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
            <div>
                <h2 class="h5 fw-bold text-slate-800 mb-1">เลือกอุปกรณ์</h2>
                <p class="text-xs text-slate-500 mb-0">แสดงเฉพาะอุปกรณ์ที่พร้อมให้ยืม · เลือกได้สูงสุด {{ $maxItems }} รายการ</p>
            </div>
            <span class="badge badge-soft-primary" data-selected-count>เลือกแล้ว 0 รายการ</span>
        </div>

        @if ($equipment->isEmpty())
            <div class="p-5 text-center">
                <div class="icon-circle icon-circle-xl icon-circle-bg-warning-soft mx-auto mb-3">
                    <i data-lucide="package-x" class="lucide-lg text-warning"></i>
                </div>
                <h3 class="h6 fw-semibold mb-1">ยังไม่มีอุปกรณ์ที่พร้อมให้ยืม</h3>
                <p class="text-muted small mb-0">กรุณาติดต่อเจ้าหน้าที่หรือลองใหม่ภายหลัง</p>
            </div>
        @else
            @php($selectedEquipment = array_map('strval', (array) old('equipment_ids', [])))
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 52px"><span class="visually-hidden">เลือก</span></th>
                            <th>รหัส</th>
                            <th>อุปกรณ์</th>
                            <th>หมวดหมู่</th>
                            <th>สถานที่</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($equipment as $item)
                            <tr>
                                <td>
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="equipment_ids[]"
                                        value="{{ $item->id }}"
                                        aria-label="เลือก {{ $item->name }}"
                                        @checked(in_array((string) $item->id, $selectedEquipment, true))
                                        data-equipment-choice
                                    >
                                </td>
                                <td class="fw-semibold text-primary">{{ $item->equipment_code }}</td>
                                <td>
                                    <div class="fw-semibold text-slate-800">{{ $item->name }}</div>
                                    <div class="text-xs text-slate-500">{{ collect([$item->brand, $item->model])->filter()->join(' · ') }}</div>
                                </td>
                                <td>{{ $item->category->name }}</td>
                                <td>{{ $item->location ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('[data-equipment-choice]');
    const counter = document.querySelector('[data-selected-count]');
    const termsCheckbox = document.querySelector('[data-borrow-terms]');
    const submitButton = document.querySelector('[data-borrow-submit]');
    const hasEquipment = submitButton?.dataset.hasEquipment === '1';
    const updateSubmitState = () => {
        if (submitButton) submitButton.disabled = !hasEquipment || !termsCheckbox?.checked;
    };
    const updateCount = () => {
        const count = Array.from(checkboxes).filter((checkbox) => checkbox.checked).length;
        if (counter) counter.textContent = `เลือกแล้ว ${count} รายการ`;
        checkboxes.forEach((checkbox) => {
            checkbox.disabled = !checkbox.checked && count >= {{ $maxItems }};
        });
    };

    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateCount));
    termsCheckbox?.addEventListener('change', updateSubmitState);
    updateCount();
    updateSubmitState();
});
</script>
@endpush
