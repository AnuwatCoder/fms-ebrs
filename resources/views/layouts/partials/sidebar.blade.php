@php
    $navigationGroups = app(\App\Support\Navigation\AppMenu::class)->for(auth()->user());
@endphp

<aside class="app-sidebar" data-sidebar>
    <a href="{{ route('dashboard') }}" class="sidebar-brand text-decoration-none" aria-label="FMS EBRS หน้าหลัก">
        <x-brand-wordmark class="sidebar-wordmark" />
    </a>

    <nav class="sidebar-nav custom-scrollbar" aria-label="เมนูหลัก">
        @foreach ($navigationGroups as $group)
            <div class="sidebar-group-title" data-menu-group="{{ $group['key'] }}">{{ $group['label'] }}</div>

            @foreach ($group['items'] as $item)
                @if (isset($item['route']))
                    <a
                        href="{{ route($item['route']) }}"
                        class="sidebar-nav-link {{ request()->routeIs($item['active'] ?? $item['route']) ? 'active' : '' }}"
                        data-menu-key="{{ $item['key'] }}"
                    >
                        <i data-lucide="{{ $item['icon'] }}" class="lucide-md"></i>
                        <span class="link-label">{{ $item['label'] }}</span>
                    </a>
                @else
                    <span
                        class="sidebar-nav-link is-disabled"
                        role="link"
                        aria-disabled="true"
                        title="{{ $item['label'] }} — พร้อมใช้งานใน Phase ถัดไป"
                        data-menu-key="{{ $item['key'] }}"
                    >
                        <i data-lucide="{{ $item['icon'] }}" class="lucide-md"></i>
                        <span class="link-label">{{ $item['label'] }}</span>
                        <span class="menu-status">เร็ว ๆ นี้</span>
                    </span>
                @endif
            @endforeach
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="user-mini">
            <div class="user-avatar-mini">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="sidebar-footer-info flex-grow-1 overflow-hidden">
                <div class="user-mini-name">{{ auth()->user()->name }}</div>
                <div class="user-mini-email">{{ auth()->user()->email ?? auth()->user()->username }}</div>
            </div>
            <i data-lucide="user" class="lucide-sm sidebar-footer-info"></i>
        </div>
    </div>
</aside>
