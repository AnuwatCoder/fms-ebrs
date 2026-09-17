@extends('layouts.app')

@section('title', 'ผู้ใช้งาน')
@section('page-title', 'ผู้ใช้งาน')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">ผู้ใช้งาน</h1>
    <p class="text-muted mb-0">จัดการสถานะบัญชีและบทบาทของผู้ใช้ที่เข้าสู่ระบบผ่าน Authentik</p>
</section>

<section class="card p-4">
    <form method="GET" action="{{ route('admin.users') }}" class="row g-3 align-items-end">
        <div class="col-12 col-lg-5">
            <label for="search" class="form-label">ค้นหา</label>
            <input id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="ชื่อ อีเมล หรือ username">
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <label for="role" class="form-label">บทบาท</label>
            <select id="role" name="role" class="form-select">
                <option value="">ทุกบทบาท</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected((string) request('role') === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="active" class="form-label">สถานะบัญชี</label>
            <select id="active" name="active" class="form-select">
                <option value="">ทั้งหมด</option>
                <option value="1" @selected(request('active') === '1')>เปิดใช้งาน</option>
                <option value="0" @selected(request('active') === '0')>ระงับ</option>
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">ค้นหา</button>
            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary"><i data-lucide="rotate-ccw" class="lucide-sm"></i></a>
        </div>
    </form>
</section>

<section class="card overflow-hidden">
    <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
        <div><h2 class="h5 fw-bold mb-1">บัญชีผู้ใช้</h2><p class="text-xs text-slate-500 mb-0">พบ {{ number_format($users->total()) }} บัญชี</p></div>
        <i data-lucide="users" class="lucide-md text-primary"></i>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>ผู้ใช้</th><th>Username</th><th>บทบาท</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><div class="fw-semibold">{{ $user->name }}</div><div class="text-xs text-slate-500">{{ $user->email ?: 'ไม่มีอีเมล' }}</div></td>
                        <td>{{ $user->username ?: '—' }}</td>
                        <td>
                            @forelse ($user->roles as $role)
                                <span class="badge badge-soft-primary me-1">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">ยังไม่มีบทบาท</span>
                            @endforelse
                        </td>
                        <td><span class="badge {{ $user->active ? 'badge-soft-success' : 'badge-soft-danger' }}">{{ $user->active ? 'เปิดใช้งาน' : 'ระงับ' }}</span></td>
                        <td class="text-end"><a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">แก้ไข</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-5 text-center text-muted">ไม่พบผู้ใช้งาน</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())<div class="p-4 border-top">{{ $users->links() }}</div>@endif
</section>
@endsection
