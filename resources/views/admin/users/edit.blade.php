@extends('layouts.app')

@section('title', 'แก้ไขผู้ใช้งาน')
@section('page-title', 'แก้ไขผู้ใช้งาน')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div><h1 class="h3 fw-bold text-slate-800 mb-1">แก้ไขผู้ใช้งาน</h1><p class="text-muted mb-0">{{ $managedUser->name }} · {{ $managedUser->email }}</p></div>
    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary"><i data-lucide="arrow-left" class="lucide-sm me-1"></i>กลับ</a>
</section>

<form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="card p-4">
    @csrf
    @method('PATCH')
    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <h2 class="h5 fw-bold mb-3">สถานะบัญชี</h2>
            <input type="hidden" name="active" value="0">
            <div class="form-check form-switch">
                <input id="active" type="checkbox" name="active" value="1" class="form-check-input" @checked((string) old('active', $managedUser->active ? '1' : '0') === '1')>
                <label for="active" class="form-check-label">อนุญาตให้เข้าสู่ระบบ</label>
            </div>
            <p class="text-xs text-slate-500 mt-2">บัญชีที่ถูกระงับจะไม่สามารถเข้าสู่ระบบได้ แม้ยืนยันตัวตนผ่าน Authentik สำเร็จ</p>
        </div>
        <div class="col-12 col-lg-8">
            <h2 class="h5 fw-bold mb-3">บทบาท</h2>
            @php($selectedRoles = array_map('strval', (array) old('roles', $managedUser->roles->pluck('id')->all())))
            <div class="row g-3">
                @foreach ($roles as $role)
                    <div class="col-12 col-md-6">
                        <label class="card p-3 d-flex flex-row align-items-center gap-3 mb-0">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="form-check-input mt-0" @checked(in_array((string) $role->id, $selectedRoles, true))>
                            <span><strong class="d-block">{{ $role->name }}</strong><span class="text-xs text-slate-500">กำหนดสิทธิ์ตามบทบาทนี้</span></span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">ยกเลิก</a>
        <button class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
    </div>
</form>
@endsection
