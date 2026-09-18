@php
    $roleSimulation = request()->attributes->get(\App\Services\Authorization\RoleSimulationService::REQUEST_ATTRIBUTE, []);
@endphp

<header class="app-header">
    <a href="{{ route('dashboard') }}" class="header-mobile-logo d-lg-none" aria-label="FMS EBRS หน้าหลัก">
        <x-brand-wordmark class="header-wordmark" />
    </a>
    <button class="header-icon-btn d-lg-none" data-toggle-mobile-sidebar aria-label="เปิดเมนู">
        <i data-lucide="menu" class="lucide-md"></i>
    </button>
    <button class="header-icon-btn d-none d-lg-flex" data-toggle-sidebar aria-label="ย่อหรือขยายเมนู">
        <i data-lucide="menu" class="lucide-md"></i>
    </button>

    <nav class="header-breadcrumb d-none d-lg-flex" aria-label="breadcrumb">
        @foreach ($headerBreadcrumbs as $breadcrumb)
            <a href="{{ $breadcrumb['url'] }}" class="crumb-parent">{{ $breadcrumb['label'] }}</a>
            <i data-lucide="chevron-right" class="lucide-sm" aria-hidden="true"></i>
        @endforeach
        <span class="crumb-last" aria-current="page">@yield('page-title', 'แดชบอร์ด')</span>
    </nav>

    <div class="flex-grow-1"></div>

    <button class="header-icon-btn" data-toggle-dark aria-label="สลับโหมดสี">
        <i data-lucide="moon" class="lucide-md"></i>
    </button>

    <div class="dropdown">
        <button class="header-icon-btn position-relative" data-bs-toggle="dropdown" aria-label="การแจ้งเตือน">
            <i data-lucide="bell" class="lucide-md"></i>
            @if ($unreadNotificationCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
            @endif
        </button>
        <div class="dropdown-menu dropdown-menu-end notif-menu">
            <div class="notif-header">
                <strong>การแจ้งเตือน</strong>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-soft-primary">{{ number_format($unreadNotificationCount) }} ใหม่</span>
                    @if ($unreadNotificationCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0 text-xs">อ่านทั้งหมด</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="notif-list custom-scrollbar">
                @forelse ($headerNotifications as $notification)
                    @php($notificationData = $notification->data)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="notif-item w-100 border-0 text-start {{ $notification->read_at ? 'bg-transparent' : 'bg-light-subtle' }}">
                            <div class="icon-circle icon-circle-sm icon-circle-bg-{{ $notificationData['tone'] ?? 'primary' }}-soft">
                                <i data-lucide="{{ $notificationData['icon'] ?? 'bell' }}" class="lucide-sm text-{{ $notificationData['tone'] ?? 'primary' }}"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="notif-title">{{ $notificationData['title'] ?? 'การแจ้งเตือน' }}</div>
                                <div class="notif-msg">{{ $notificationData['message'] ?? '' }}</div>
                                <div class="text-xs text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                        </button>
                    </form>
                @empty
                    <div class="notif-item">
                        <div class="icon-circle icon-circle-sm icon-circle-bg-info-soft">
                            <i data-lucide="bell-off" class="lucide-sm"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="notif-title">ยังไม่มีการแจ้งเตือน</div>
                            <div class="notif-msg">รายการใหม่จะแสดงที่นี่</div>
                        </div>
                    </div>
                @endforelse
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
                <div class="user-role">
                    @if ($roleSimulation['active'] ?? null)
                        จำลอง: {{ $roleSimulation['active_label'] }}
                    @else
                        {{ auth()->user()->getRoleNames()->implode(', ') ?: 'User' }}
                    @endif
                </div>
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
            @if ($roleSimulation['allowed'] ?? false)
                <div class="dropdown-divider"></div>
                <div class="px-3 py-2">
                    <div class="small fw-semibold mb-2">จำลองมุมมองบทบาท</div>
                    <div class="d-grid gap-1">
                        @foreach ($roleSimulation['roles'] as $role)
                            <form method="POST" action="{{ route('admin.role-simulation.store') }}">
                                @csrf
                                <input type="hidden" name="role" value="{{ $role['name'] }}">
                                <button
                                    type="submit"
                                    class="btn btn-sm w-100 text-start {{ ($roleSimulation['active'] ?? null) === $role['name'] ? 'btn-primary' : 'btn-outline-secondary' }}"
                                    @disabled(($roleSimulation['active'] ?? null) === $role['name'])
                                >
                                    {{ $role['label'] }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif
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
