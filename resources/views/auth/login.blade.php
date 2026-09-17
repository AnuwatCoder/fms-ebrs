@extends('layouts.auth')

@section('title', 'เข้าสู่ระบบ')

@section('content')
<div class="auth-min-vh d-flex">
    <div class="flex-grow-1 d-flex align-items-center justify-content-center p-4 p-md-5 bg-white">
        <div class="w-100 max-w-420">
            <div class="auth-brand justify-content-start mb-4">
                <img src="{{ asset('template/img/logo.svg') }}" alt="EBRS" class="logo logo-img">
                <span class="fs-3 fw-bold text-primary">EBRS</span>
            </div>

            <h1 class="fw-bold mb-2 fs-2">เข้าสู่ระบบ</h1>
            <p class="text-muted mb-4">ระบบยืม–คืนอุปกรณ์ภายในองค์กร</p>

            @include('layouts.partials.alerts')

            <div class="card border-0 shadow-sm p-4 mb-4">
                <div class="icon-circle icon-circle-xl icon-circle-bg-primary-soft mb-3">
                    <i data-lucide="shield-check" class="lucide-lg text-primary"></i>
                </div>
                <h2 class="h5 fw-bold mb-2">ลงชื่อเข้าใช้ด้วยบัญชีองค์กร</h2>
                <p class="text-muted small mb-4">
                    ระบบใช้ Authentik ผ่าน OpenID Connect และจะนำคุณไปยืนยันตัวตนกับบัญชีองค์กรอย่างปลอดภัย
                </p>
                <a href="{{ route('auth.authentik.redirect') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2">
                    เข้าสู่ระบบด้วย Authentik
                    <i data-lucide="arrow-right" class="lucide-sm"></i>
                </a>
            </div>

            <p class="footer-credit text-center mb-0">
                หากไม่สามารถเข้าสู่ระบบได้ กรุณาติดต่อผู้ดูแลระบบ
            </p>
        </div>
    </div>

    <div class="d-none d-xl-flex flex-grow-1 align-items-center justify-content-center p-5 auth-right-wrapper bg-gradient-auth-right">
        <div class="blob-wrap"><div class="blob-1"></div><div class="blob-2"></div></div>
        <div class="auth-right-content">
            <div class="row g-3 mb-4">
                <div class="col-4"><div class="glass-tile"><i data-lucide="search" class="lucide-lg mb-2"></i><div class="fw-bold">ค้นหา</div><div class="text-xs opacity-75">อุปกรณ์</div></div></div>
                <div class="col-4"><div class="glass-tile"><i data-lucide="clipboard-check" class="lucide-lg mb-2"></i><div class="fw-bold">อนุมัติ</div><div class="text-xs opacity-75">คำขอยืม</div></div></div>
                <div class="col-4"><div class="glass-tile"><i data-lucide="scan-line" class="lucide-lg mb-2"></i><div class="fw-bold">สแกน</div><div class="text-xs opacity-75">QR Code</div></div></div>
                <div class="col-4"><div class="glass-tile"><i data-lucide="package-check" class="lucide-lg mb-2"></i><div class="fw-bold">รับคืน</div><div class="text-xs opacity-75">ตรวจสภาพ</div></div></div>
                <div class="col-4"><div class="glass-tile"><i data-lucide="history" class="lucide-lg mb-2"></i><div class="fw-bold">ติดตาม</div><div class="text-xs opacity-75">ประวัติ</div></div></div>
                <div class="col-4"><div class="glass-tile"><i data-lucide="shield-check" class="lucide-lg mb-2"></i><div class="fw-bold">ปลอดภัย</div><div class="text-xs opacity-75">RBAC</div></div></div>
            </div>
            <h2 class="fw-bold mb-2">Equipment Borrowing &amp; Return System</h2>
            <p class="opacity-75 small mb-0">บริหารอุปกรณ์ คำขอยืม การอนุมัติ การจ่าย และการรับคืนในระบบเดียว</p>
        </div>
    </div>
</div>
@endsection
