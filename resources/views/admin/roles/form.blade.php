@extends('layouts.app')

@php($editing = $role->exists)
@section('title', $editing ? 'แก้ไขบทบาท' : 'สร้างบทบาท')
@section('page-title', $editing ? 'แก้ไขบทบาท' : 'สร้างบทบาท')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div><h1 class="h3 fw-bold text-slate-800 mb-1">{{ $editing ? 'แก้ไขบทบาท' : 'สร้างบทบาท' }}</h1><p class="text-muted mb-0">เลือกสิทธิ์ที่บทบาทนี้สามารถใช้งานได้</p></div>
    <a href="{{ route('admin.roles') }}" class="btn btn-outline-secondary"><i data-lucide="arrow-left" class="lucide-sm me-1"></i>กลับ</a>
</section>

<form method="POST" action="{{ $editing ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="card p-4">
    @csrf
    @if ($editing) @method('PATCH') @endif
    <div class="mb-4">
        <label for="name" class="form-label">ชื่อบทบาท <span class="text-danger">*</span></label>
        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required @readonly($editing && in_array($role->name, $coreRoles, true))>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @php($selected = array_map('strval', (array) old('permissions', $selectedPermissions->all())))
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h5 fw-bold mb-0">สิทธิ์การใช้งาน</h2>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle-permissions>เลือก/ยกเลิกทั้งหมด</button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach ($permissionGroups as $group => $permissions)
            <section class="border rounded-3 p-3">
                <h3 class="h6 fw-bold text-primary text-uppercase mb-3">{{ $group }}</h3>
                <div class="d-flex flex-column gap-2">
                    @foreach ($permissions as $permission)
                        <label class="form-check">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-check-input" @checked(in_array((string) $permission->id, $selected, true))>
                            <span class="form-check-label">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
        <a href="{{ route('admin.roles') }}" class="btn btn-outline-secondary">ยกเลิก</a>
        <button class="btn btn-primary">บันทึกบทบาท</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.querySelector('[data-toggle-permissions]')?.addEventListener('click', function () {
    const boxes = Array.from(document.querySelectorAll('input[name="permissions[]"]'));
    const shouldCheck = boxes.some((box) => !box.checked);
    boxes.forEach((box) => { box.checked = shouldCheck; });
});
</script>
@endpush
