@extends('layouts.app')

@php($editing = $borrowRequest->exists)

@section('title', $editing ? 'แก้ไขฉบับร่างคำขอยืม' : 'สร้างคำขอยืม')
@section('page-title', $editing ? 'แก้ไขฉบับร่างคำขอยืม' : 'สร้างคำขอยืม')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">{{ $editing ? 'แก้ไขฉบับร่าง '.$borrowRequest->request_no : 'สร้างคำขอยืม' }}</h1>
    <p class="text-muted mb-0">ระบุช่วงเวลาการใช้งาน เลือกอุปกรณ์ และบันทึกไว้เป็นฉบับร่างหรือส่งอนุมัติ</p>
</section>

<form method="POST" action="{{ $editing ? route('borrow.update', $borrowRequest) : route('borrow.store') }}" class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    @csrf
    @if ($editing)
        @method('PATCH')
    @endif

    <section class="xl:col-span-1 card p-4 align-self-start">
        <div class="d-flex align-items-center gap-2 mb-4">
            <div class="icon-circle icon-circle-sm icon-circle-bg-primary-soft">
                <i data-lucide="clipboard-pen-line" class="lucide-sm text-primary"></i>
            </div>
            <h2 class="h5 fw-bold text-slate-800 mb-0">รายละเอียดคำขอ</h2>
        </div>

        <div class="mb-3">
            <label for="purpose" class="form-label">วัตถุประสงค์ <span class="text-danger">*</span></label>
            <textarea id="purpose" name="purpose" rows="4" class="form-control @error('purpose') is-invalid @enderror" required>{{ old('purpose', $borrowRequest->purpose) }}</textarea>
            @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="usage_location" class="form-label">สถานที่นำไปใช้งาน</label>
            <input id="usage_location" name="usage_location" class="form-control" value="{{ old('usage_location', $borrowRequest->usage_location) }}">
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
                    value="{{ old('borrow_date', $borrowRequest->borrow_date?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                    required
                >
                @error('borrow_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6 col-xl-12">
                <label for="expected_return_date" class="form-label">กำหนดคืน <span class="text-danger">*</span></label>
                <input
                    id="expected_return_date"
                    type="date"
                    name="expected_return_date"
                    class="form-control @error('expected_return_date') is-invalid @enderror"
                    min="{{ today()->format('Y-m-d') }}"
                    value="{{ old('expected_return_date', $borrowRequest->expected_return_date?->format('Y-m-d') ?? today()->addDays($defaultLoanDays)->format('Y-m-d')) }}"
                    required
                >
                @error('expected_return_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-4">
            <label for="note" class="form-label">หมายเหตุ</label>
            <textarea id="note" name="note" rows="3" class="form-control">{{ old('note', $borrowRequest->note) }}</textarea>
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

        <div class="d-grid gap-2">
            <button
                type="submit"
                name="intent"
                value="draft"
                class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2"
                formnovalidate
            >
                <i data-lucide="save" class="lucide-sm"></i>
                บันทึกฉบับร่าง
            </button>
            <button
                type="submit"
                name="intent"
                value="submit"
                class="btn btn-primary d-flex align-items-center justify-content-center gap-2"
                data-borrow-submit
                @disabled($equipment->isEmpty() || empty(old('equipment_ids', $selectedEquipmentIds)) || ! old('accept_terms'))
            >
                <i data-lucide="send" class="lucide-sm"></i>
                ส่งคำขออนุมัติ
            </button>
        </div>
    </section>

    <section class="xl:col-span-2 card overflow-hidden">
        <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
            <div>
                <h2 class="h5 fw-bold text-slate-800 mb-1">เลือกอุปกรณ์</h2>
                <p class="text-xs text-slate-500 mb-0">ตรวจสอบตามช่วงวันที่เลือก · เลือกได้สูงสุด {{ $maxItems }} รายการ</p>
            </div>
            <span class="badge badge-soft-primary" data-selected-count>เลือกแล้ว 0 รายการ</span>
        </div>

        <div
            class="alert {{ $equipment->isEmpty() ? 'alert-warning' : 'alert-info' }} rounded-0 border-0 border-bottom mb-0"
            role="status"
            aria-live="polite"
            data-availability-feedback
        >
            {{ $equipment->isEmpty() ? 'ไม่พบอุปกรณ์ว่างในช่วงวันที่เลือก กรุณาเปลี่ยนช่วงวันที่หรือติดต่อเจ้าหน้าที่' : 'พบอุปกรณ์ว่าง '.$equipment->count().' รายการ' }}
        </div>

        <div class="p-5 text-center {{ $equipment->isNotEmpty() ? 'd-none' : '' }}" data-availability-empty>
            <div class="icon-circle icon-circle-xl icon-circle-bg-warning-soft mx-auto mb-3">
                <i data-lucide="package-x" class="lucide-lg text-warning"></i>
            </div>
            <h3 class="h6 fw-semibold mb-1">ไม่มีอุปกรณ์ว่างในช่วงวันที่เลือก</h3>
            <p class="text-muted small mb-0">กรุณาเปลี่ยนช่วงวันที่หรือติดต่อเจ้าหน้าที่</p>
        </div>

        @php($selectedEquipment = array_map('strval', (array) old('equipment_ids', $selectedEquipmentIds)))
        <div class="table-responsive {{ $equipment->isEmpty() ? 'd-none' : '' }}" data-availability-table>
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
                <tbody data-equipment-list>
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
    </section>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const availabilityUrl = @js(route('borrow.availability'));
    const maxItems = @js($maxItems);
    const borrowDate = document.getElementById('borrow_date');
    const returnDate = document.getElementById('expected_return_date');
    const equipmentList = document.querySelector('[data-equipment-list]');
    const equipmentTable = document.querySelector('[data-availability-table]');
    const emptyState = document.querySelector('[data-availability-empty]');
    const feedback = document.querySelector('[data-availability-feedback]');
    const counter = document.querySelector('[data-selected-count]');
    const termsCheckbox = document.querySelector('[data-borrow-terms]');
    const submitButton = document.querySelector('[data-borrow-submit]');
    let checkboxes = [];
    let activeRequest = null;

    const selectedCount = () => checkboxes.filter((checkbox) => checkbox.checked).length;
    const updateSubmitState = () => {
        if (submitButton) {
            submitButton.disabled = checkboxes.length === 0 || selectedCount() === 0 || !termsCheckbox?.checked;
        }
    };
    const updateCount = () => {
        const count = selectedCount();
        if (counter) counter.textContent = `เลือกแล้ว ${count} รายการ`;
        checkboxes.forEach((checkbox) => {
            checkbox.disabled = !checkbox.checked && count >= maxItems;
        });
        updateSubmitState();
    };
    const bindChoices = () => {
        checkboxes = Array.from(document.querySelectorAll('[data-equipment-choice]'));
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateCount));
        updateCount();
    };
    const appendTextCell = (row, text, className = '') => {
        const cell = document.createElement('td');
        cell.textContent = text;
        cell.className = className;
        row.appendChild(cell);
    };
    const renderEquipment = (items) => {
        const previouslySelected = new Set(checkboxes.filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value));
        equipmentList.replaceChildren();

        items.forEach((item) => {
            const row = document.createElement('tr');
            const choiceCell = document.createElement('td');
            const choice = document.createElement('input');
            choice.type = 'checkbox';
            choice.name = 'equipment_ids[]';
            choice.value = String(item.id);
            choice.className = 'form-check-input';
            choice.dataset.equipmentChoice = '';
            choice.setAttribute('aria-label', `เลือก ${item.name}`);
            choice.checked = previouslySelected.has(choice.value);
            choiceCell.appendChild(choice);
            row.appendChild(choiceCell);

            appendTextCell(row, item.equipment_code, 'fw-semibold text-primary');

            const nameCell = document.createElement('td');
            const name = document.createElement('div');
            name.className = 'fw-semibold text-slate-800';
            name.textContent = item.name;
            const detail = document.createElement('div');
            detail.className = 'text-xs text-slate-500';
            detail.textContent = [item.brand, item.model].filter(Boolean).join(' · ');
            nameCell.append(name, detail);
            row.appendChild(nameCell);

            appendTextCell(row, item.category.name);
            appendTextCell(row, item.location || '—');
            equipmentList.appendChild(row);
        });

        equipmentTable.classList.toggle('d-none', items.length === 0);
        emptyState.classList.toggle('d-none', items.length > 0);
        bindChoices();
    };
    const setFeedback = (message, type) => {
        feedback.textContent = message;
        feedback.className = `alert alert-${type} rounded-0 border-0 border-bottom mb-0`;
    };
    const loadAvailability = async () => {
        if (!borrowDate.value || !returnDate.value || returnDate.value < borrowDate.value) {
            renderEquipment([]);
            setFeedback('กรุณาเลือกช่วงวันที่ให้ถูกต้องก่อนตรวจสอบอุปกรณ์', 'warning');
            return;
        }

        activeRequest?.abort();
        activeRequest = new AbortController();
        setFeedback('กำลังตรวจสอบอุปกรณ์ว่าง...', 'info');

        try {
            const params = new URLSearchParams({
                borrow_date: borrowDate.value,
                expected_return_date: returnDate.value,
            });
            const response = await fetch(`${availabilityUrl}?${params}`, {
                headers: { Accept: 'application/json' },
                signal: activeRequest.signal,
            });
            const payload = await response.json();

            if (!response.ok) {
                const errors = Object.values(payload.errors || {}).flat();
                throw new Error(errors[0] || 'ไม่สามารถตรวจสอบอุปกรณ์ได้');
            }

            renderEquipment(payload.data || []);
            setFeedback(payload.meta.message, payload.meta.count > 0 ? 'info' : 'warning');
        } catch (error) {
            if (error.name === 'AbortError') return;

            renderEquipment([]);
            setFeedback(error.message || 'ไม่สามารถตรวจสอบอุปกรณ์ได้ กรุณาลองใหม่', 'danger');
        }
    };
    const handleBorrowDateChange = () => {
        returnDate.min = borrowDate.value;
        if (returnDate.value < borrowDate.value) returnDate.value = borrowDate.value;
        loadAvailability();
    };

    borrowDate?.addEventListener('change', handleBorrowDateChange);
    returnDate?.addEventListener('change', loadAvailability);
    termsCheckbox?.addEventListener('change', updateSubmitState);
    bindChoices();
    loadAvailability();
});
</script>
@endpush
