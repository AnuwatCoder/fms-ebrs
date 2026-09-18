@extends('layouts.auth')

@section('title', 'เข้าสู่ระบบ')

@section('content')
<main class="login-shell">
    <div class="login-ambient" aria-hidden="true">
        <span class="login-ambient-orb login-ambient-orb-one"></span>
        <span class="login-ambient-orb login-ambient-orb-two"></span>
    </div>

    <section class="login-panel" aria-labelledby="login-title">
        <div class="login-panel-inner">
            <a href="{{ route('login') }}" class="login-brand" aria-label="FMS EBRS หน้าเข้าสู่ระบบ">
                <x-brand-wordmark />
                <span class="login-brand-divider" aria-hidden="true"></span>
                <small>Equipment Borrowing<br>&amp; Return System</small>
            </a>

            <div class="login-intro">
                <span class="login-eyebrow">
                    <i data-lucide="sparkles" aria-hidden="true"></i>
                    ยินดีต้อนรับกลับ
                </span>
                <h1 id="login-title">เข้าสู่ระบบเพื่อเริ่มจัดการอุปกรณ์</h1>
                <p>เข้าถึงคำขอยืม การอนุมัติ การจ่าย และการรับคืน ผ่านบัญชีองค์กรของคุณ</p>
            </div>

            @include('layouts.partials.alerts')

            <div class="login-card">
                <div class="login-card-heading">
                    <span class="login-security-icon" aria-hidden="true">
                        <i data-lucide="shield-check"></i>
                    </span>
                    <div>
                        <h2>ลงชื่อเข้าใช้ด้วยบัญชีองค์กร</h2>
                        <p>Single Sign-On</p>
                    </div>
                </div>

                <p class="login-card-description">
                    ระบบจะเชื่อมต่อกับ Authentik ผ่าน OpenID Connect เพื่อยืนยันตัวตนอย่างปลอดภัย โดยไม่จัดเก็บรหัสผ่านไว้ในระบบนี้
                </p>

                <a
                    href="{{ route('auth.authentik.redirect') }}"
                    class="login-action"
                    aria-describedby="login-redirect-note"
                >
                    <span>เข้าสู่ระบบด้วย Authentik</span>
                    <span class="login-action-icon" aria-hidden="true">
                        <i data-lucide="arrow-right"></i>
                    </span>
                </a>

                <div id="login-redirect-note" class="login-redirect-note">
                    <i data-lucide="external-link" aria-hidden="true"></i>
                    คุณจะถูกนำไปยังหน้าลงชื่อเข้าใช้ขององค์กร
                </div>
            </div>

            <div class="login-trust-row" aria-label="คุณสมบัติด้านความปลอดภัย">
                <span><i data-lucide="lock-keyhole" aria-hidden="true"></i> เชื่อมต่อแบบเข้ารหัส</span>
                <span><i data-lucide="badge-check" aria-hidden="true"></i> ควบคุมสิทธิ์ตามบทบาท</span>
            </div>

            <p class="login-help">
                หากไม่สามารถเข้าสู่ระบบได้ กรุณาติดต่อผู้ดูแลระบบ
            </p>
        </div>
    </section>

    <aside class="login-showcase" aria-label="ภาพรวมระบบยืมและคืนอุปกรณ์">
        <div class="login-showcase-grid" aria-hidden="true"></div>
        <div class="login-showcase-glow" aria-hidden="true"></div>
        <span class="login-showcase-orb login-showcase-orb-one" aria-hidden="true"></span>
        <span class="login-showcase-orb login-showcase-orb-two" aria-hidden="true"></span>

        <div class="login-showcase-content">
            <div class="login-showcase-top">
                <span class="login-sso-pill">
                    <span class="login-live-dot" aria-hidden="true"></span>
                    Secure Single Sign-On
                </span>
                <span class="login-version">FMS EBRS</span>
            </div>

            <div class="login-showcase-copy">
                <span class="login-showcase-eyebrow">ONE CONNECTED WORKFLOW</span>
                <h2>จัดการการยืมอุปกรณ์<br>ได้ครบในที่เดียว</h2>
                <p>ลดขั้นตอนที่ซ้ำซ้อน พร้อมติดตามสถานะของทุกคำขอได้อย่างชัดเจน</p>
            </div>

            <div class="login-workflow-card">
                <div class="login-workflow-header">
                    <div>
                        <span>ขั้นตอนการยืม</span>
                        <strong>ดำเนินการอย่างเป็นระบบ</strong>
                    </div>
                    <span class="login-workflow-badge">3 ขั้นตอน</span>
                </div>

                <ol class="login-workflow-list">
                    <li class="login-workflow-step-one">
                        <span class="login-step-icon" aria-hidden="true"><i data-lucide="search"></i></span>
                        <span class="login-step-copy"><strong>ค้นหาและเลือก</strong><small>ตรวจสอบอุปกรณ์ว่างตามวันที่</small></span>
                        <i data-lucide="check" class="login-step-check" aria-hidden="true"></i>
                    </li>
                    <li class="login-workflow-step-two">
                        <span class="login-step-icon" aria-hidden="true"><i data-lucide="clipboard-check"></i></span>
                        <span class="login-step-copy"><strong>ส่งคำขออนุมัติ</strong><small>ติดตามสถานะได้ทุกขั้นตอน</small></span>
                        <i data-lucide="check" class="login-step-check" aria-hidden="true"></i>
                    </li>
                    <li class="login-workflow-step-three">
                        <span class="login-step-icon" aria-hidden="true"><i data-lucide="package-check"></i></span>
                        <span class="login-step-copy"><strong>รับและคืนอุปกรณ์</strong><small>บันทึกประวัติและผลการตรวจสภาพ</small></span>
                        <span class="login-step-current" aria-hidden="true"></span>
                    </li>
                </ol>
            </div>

            <div class="login-showcase-footer">
                <div><i data-lucide="history" aria-hidden="true"></i><span><strong>ตรวจสอบย้อนหลัง</strong><small>ประวัติครบถ้วน</small></span></div>
                <div><i data-lucide="users-round" aria-hidden="true"></i><span><strong>สิทธิ์ตามบทบาท</strong><small>เข้าถึงอย่างเหมาะสม</small></span></div>
            </div>
        </div>
    </aside>
</main>
@endsection
