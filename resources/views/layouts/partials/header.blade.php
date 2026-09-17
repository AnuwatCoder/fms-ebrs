<header class="app-header">
    <a href="{{ route('dashboard') }}" class="header-mobile-logo d-lg-none" aria-label="EBRS">
        <img src="{{ asset('template/img/logo.svg') }}" alt="EBRS" width="28" height="28">
    </a>
    <button class="header-icon-btn d-lg-none" data-toggle-mobile-sidebar aria-label="เปิดเมนู">
        <i data-lucide="menu" class="lucide-md"></i>
    </button>
    <button class="header-icon-btn d-none d-lg-flex" data-toggle-sidebar aria-label="ย่อหรือขยายเมนู">
        <i data-lucide="menu" class="lucide-md"></i>
    </button>

    <div class="header-breadcrumb d-none d-md-flex">
        <span class="text-muted">หน้าหลัก</span>
        <i data-lucide="chevron-right" class="lucide-sm"></i>
        <span class="crumb-last">@yield('page-title', 'แดชบอร์ด')</span>
    </div>

    <div class="flex-grow-1"></div>

    <button class="header-icon-btn" data-toggle-dark aria-label="สลับโหมดสี">
        <i data-lucide="moon" class="lucide-md"></i>
    </button>

    <div class="dropdown">
        <button class="header-icon-btn" data-bs-toggle="dropdown" aria-label="การแจ้งเตือน">
            <i data-lucide="bell" class="lucide-md"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end notif-menu">
            <div class="notif-header">
                <strong>การแจ้งเตือน</strong>
                <span class="badge badge-soft-primary">0 ใหม่</span>
            </div>
            <div class="notif-list custom-scrollbar">
                <div class="notif-item">
                    <div class="icon-circle icon-circle-sm icon-circle-bg-info-soft">
                        <i data-lucide="bell-off" class="lucide-sm"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="notif-title">ยังไม่มีการแจ้งเตือน</div>
                        <div class="notif-msg">รายการใหม่จะแสดงที่นี่</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button class="header-icon-btn" data-toggle-settings aria-label="ตั้งค่าหน้าจอ">
        <i data-lucide="settings" class="lucide-md"></i>
    </button>

    <div class="dropdown">
        <button class="user-button" data-bs-toggle="dropdown">
            <div class="avatar avatar-sm">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="d-none d-sm-block text-start">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->getRoleNames()->implode(', ') ?: 'User' }}</div>
            </div>
            <i data-lucide="chevron-down" class="lucide-sm d-none d-sm-inline"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end user-menu">
            <div class="user-menu-header">
                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                <div class="text-muted small">{{ auth()->user()->email ?? auth()->user()->username }}</div>
            </div>
            <a class="dropdown-item user-menu-item" href="#" aria-disabled="true">
                <i data-lucide="user" class="lucide-sm"></i> โปรไฟล์
            </a>
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item user-menu-item text-danger">
                    <i data-lucide="log-out" class="lucide-sm"></i> ออกจากระบบ
                </button>
            </form>
        </div>
    </div>
</header>
