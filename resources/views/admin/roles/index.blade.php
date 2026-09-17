@extends('layouts.app')

@section('title', 'บทบาท')
@section('page-title', 'บทบาท')

@section('content')
<section class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div><h1 class="h3 fw-bold text-slate-800 mb-1">บทบาท</h1><p class="text-muted mb-0">กำหนดกลุ่มสิทธิ์สำหรับผู้ใช้งานแต่ละหน้าที่</p></div>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i data-lucide="plus" class="lucide-sm me-1"></i>สร้างบทบาท</a>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach ($roles as $role)
        <article class="card p-4">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                <div><h2 class="h5 fw-bold mb-1">{{ $role->name }}</h2><span class="text-xs text-slate-500">{{ in_array($role->name, $coreRoles, true) ? 'บทบาทหลักของระบบ' : 'บทบาทกำหนดเอง' }}</span></div>
                <div class="icon-circle icon-circle-sm icon-circle-bg-primary-soft"><i data-lucide="shield-check" class="lucide-sm text-primary"></i></div>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-slate-500">ผู้ใช้งาน</span><strong>{{ number_format($role->users_count) }}</strong></div>
            <div class="d-flex justify-content-between py-2 mb-3"><span class="text-slate-500">สิทธิ์</span><strong>{{ number_format($role->permissions_count) }}</strong></div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-primary flex-grow-1">แก้ไข</a>
                @unless (in_array($role->name, $coreRoles, true) || $role->users_count > 0)
                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('ยืนยันการลบบทบาทนี้?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger" aria-label="ลบ {{ $role->name }}"><i data-lucide="trash-2" class="lucide-sm"></i></button>
                    </form>
                @endunless
            </div>
        </article>
    @endforeach
</section>
@endsection
