@extends('layouts.app')

@section('title', 'สิทธิ์การใช้งาน')
@section('page-title', 'สิทธิ์การใช้งาน')

@section('content')
<section>
    <h1 class="h3 fw-bold text-slate-800 mb-1">สิทธิ์การใช้งาน</h1>
    <p class="text-muted mb-0">ตรวจสอบ permission ที่ระบบกำหนดและบทบาทที่ได้รับสิทธิ์</p>
</section>

@foreach ($permissions as $group => $groupPermissions)
    <section class="card overflow-hidden">
        <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
            <h2 class="h5 fw-bold text-uppercase mb-0">{{ $group }}</h2>
            <span class="badge badge-soft-primary">{{ $groupPermissions->count() }} สิทธิ์</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Permission</th><th>บทบาทที่ได้รับสิทธิ์</th></tr></thead>
                <tbody>
                    @foreach ($groupPermissions as $permission)
                        <tr>
                            <td><code>{{ $permission->name }}</code></td>
                            <td>
                                @forelse ($permission->roles as $role)
                                    <span class="badge badge-soft-primary me-1">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">ยังไม่มีบทบาท</span>
                                @endforelse
                                <span class="badge badge-soft-success ms-1">SuperAdmin</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endforeach
@endsection
