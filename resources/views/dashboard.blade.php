@extends('layouts.app')

@section('title', 'แดชบอร์ด')
@section('page-title', 'แดชบอร์ด')

@section('content')
<div data-dashboard-role="{{ $dashboardProfile['key'] }}">
    <section class="dashboard-hero dashboard-hero-{{ $dashboardProfile['key'] }}">
        <div class="dashboard-hero-orb dashboard-hero-orb-one"></div>
        <div class="dashboard-hero-orb dashboard-hero-orb-two"></div>
        <div class="dashboard-hero-content">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="dashboard-role-chip">
                        <i data-lucide="user-round-check" class="lucide-xs"></i>
                        {{ $dashboardProfile['role_label'] }}
                    </span>
                    <span class="dashboard-date-chip">
                        <i data-lucide="calendar-days" class="lucide-xs"></i>
                        {{ now()->locale('th')->translatedFormat('l j F Y') }}
                    </span>
                </div>
                <h1 class="display-6 fw-bold mb-2">{{ $dashboardProfile['hero_title'] }}</h1>
                <p class="dashboard-hero-copy mb-0">
                    สวัสดี {{ auth()->user()->name }} · {{ $dashboardProfile['hero_description'] }}
                </p>
            </div>

            @if ($dashboardProfile['actions'] !== [])
                <nav class="dashboard-quick-actions" aria-label="ทางลัดสำหรับ{{ $dashboardProfile['role_label'] }}">
                    @foreach ($dashboardProfile['actions'] as $action)
                        <a
                            href="{{ route($action['route']) }}"
                            class="dashboard-quick-link"
                            data-dashboard-action="{{ $action['route'] }}"
                        >
                            <i data-lucide="{{ $action['icon'] }}" class="lucide-sm"></i>
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </section>

    <section class="row g-4" aria-label="ตัวชี้วัดสำหรับ{{ $dashboardProfile['role_label'] }}">
        @foreach ($dashboardProfile['metrics'] as $metric)
            <div class="col-sm-6 col-xl-3">
                <article class="card dashboard-metric-card dashboard-metric-{{ $metric['tone'] }} h-100">
                    <div class="dashboard-metric-header">
                        <div class="dashboard-metric-icon">
                            <i data-lucide="{{ $metric['icon'] }}" class="lucide-md"></i>
                        </div>
                        <span class="dashboard-metric-label">{{ $metric['label'] }}</span>
                    </div>
                    <div class="dashboard-metric-value">{{ number_format($metric['value']) }}</div>
                    <div class="dashboard-metric-caption">{{ $metric['caption'] }}</div>
                </article>
            </div>
        @endforeach
    </section>

    <section class="row g-4">
        <div class="col-xl-8">
            <article class="card dashboard-chart-card h-100">
                <div class="dashboard-card-heading">
                    <div>
                        <div class="dashboard-eyebrow">แนวโน้ม 6 เดือน</div>
                        <h2 class="h5 fw-bold text-slate-800 mb-1">{{ $dashboardProfile['trend_title'] }}</h2>
                        <p class="text-muted text-sm mb-0">{{ $dashboardProfile['trend_description'] }}</p>
                    </div>
                    <div class="dashboard-heading-icon dashboard-heading-icon-primary">
                        <i data-lucide="chart-no-axes-combined" class="lucide-md"></i>
                    </div>
                </div>
                <div id="dashboard-request-trend" class="dashboard-chart" data-dashboard-chart="request-trend"></div>
            </article>
        </div>

        <div class="col-xl-4">
            <article class="card dashboard-chart-card h-100">
                <div class="dashboard-card-heading">
                    <div>
                        <div class="dashboard-eyebrow">การกระจายข้อมูล</div>
                        <h2 class="h5 fw-bold text-slate-800 mb-1">{{ $distributionChart['title'] }}</h2>
                        <p class="text-muted text-sm mb-0">{{ $distributionChart['subtitle'] }}</p>
                    </div>
                    <div class="dashboard-heading-icon dashboard-heading-icon-success">
                        <i data-lucide="chart-pie" class="lucide-md"></i>
                    </div>
                </div>
                <div id="dashboard-distribution" class="dashboard-chart dashboard-chart-donut" data-dashboard-chart="distribution"></div>
            </article>
        </div>
    </section>

    <section class="row g-4">
        <div class="col-xl-8">
            <article class="card overflow-hidden h-100">
                <div class="dashboard-card-heading border-bottom">
                    <div>
                        <div class="dashboard-eyebrow">กิจกรรมล่าสุด</div>
                        <h2 class="h5 fw-bold text-slate-800 mb-1">{{ $dashboardProfile['recent_title'] }}</h2>
                        <p class="text-muted text-sm mb-0">{{ $dashboardProfile['recent_description'] }}</p>
                    </div>
                    @if ($dashboardProfile['recent_route'])
                        <a href="{{ route($dashboardProfile['recent_route']) }}" class="btn btn-sm btn-light d-inline-flex align-items-center gap-2">
                            ดูทั้งหมด
                            <i data-lucide="arrow-right" class="lucide-xs"></i>
                        </a>
                    @endif
                </div>

                @if ($recentRequests->isEmpty())
                    <div class="dashboard-empty-state">
                        <div class="dashboard-empty-icon"><i data-lucide="inbox" class="lucide-lg"></i></div>
                        <h3 class="h6 fw-semibold mb-1">ยังไม่มีรายการ</h3>
                        <p class="text-muted text-sm mb-0">{{ $dashboardProfile['empty_recent'] }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" data-dashboard-table="recent-requests">
                            <thead>
                                <tr>
                                    <th>เลขที่คำขอ</th>
                                    @if ($dashboardProfile['show_borrower'])<th>ผู้ยืม</th>@endif
                                    <th>วันที่ยืม</th>
                                    <th class="text-center">จำนวน</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentRequests as $borrowRequest)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-primary">{{ $borrowRequest->request_no }}</div>
                                            <div class="text-muted text-xs text-truncate dashboard-purpose">{{ $borrowRequest->purpose }}</div>
                                        </td>
                                        @if ($dashboardProfile['show_borrower'])<td>{{ $borrowRequest->borrower->name }}</td>@endif
                                        <td>{{ $borrowRequest->borrow_date->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ number_format($borrowRequest->items_count) }}</td>
                                        <td><span class="badge {{ $borrowRequest->status->badgeClass() }}">{{ $borrowRequest->status->label() }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </div>

        <div class="col-xl-4">
            <aside class="card h-100 overflow-hidden">
                <div class="dashboard-card-heading border-bottom">
                    <div>
                        <div class="dashboard-eyebrow dashboard-eyebrow-danger">ต้องดำเนินการ</div>
                        <h2 class="h5 fw-bold text-slate-800 mb-1">{{ $dashboardProfile['attention_title'] }}</h2>
                        <p class="text-muted text-sm mb-0">{{ $dashboardProfile['attention_description'] }}</p>
                    </div>
                    <div class="dashboard-heading-icon dashboard-heading-icon-danger">
                        <i data-lucide="{{ $dashboardProfile['attention_icon'] }}" class="lucide-md"></i>
                    </div>
                </div>

                @if ($attentionRequests->isEmpty())
                    <div class="dashboard-empty-state dashboard-empty-compact">
                        <div class="dashboard-empty-icon dashboard-empty-icon-success">
                            <i data-lucide="shield-check" class="lucide-lg"></i>
                        </div>
                        <h3 class="h6 fw-semibold mb-1">สถานะเรียบร้อย</h3>
                        <p class="text-muted text-sm mb-0">{{ $dashboardProfile['empty_attention'] }}</p>
                    </div>
                @else
                    <div class="dashboard-attention-list">
                        @foreach ($attentionRequests as $borrowRequest)
                            <div class="dashboard-attention-item">
                                <div class="dashboard-attention-icon dashboard-attention-{{ $dashboardProfile['attention_mode'] }}">
                                    <i data-lucide="{{ $dashboardProfile['attention_mode'] === 'pending' ? 'clipboard-clock' : ($dashboardProfile['attention_mode'] === 'operations' ? 'package-search' : 'clock-3') }}" class="lucide-sm"></i>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <span class="fw-semibold text-slate-800">{{ $borrowRequest->request_no }}</span>
                                        @if ($dashboardProfile['attention_mode'] === 'overdue')
                                            <span class="badge badge-soft-danger">{{ (int) $borrowRequest->expected_return_date->diffInDays(today()) }} วัน</span>
                                        @else
                                            <span class="badge {{ $borrowRequest->status->badgeClass() }}">{{ $borrowRequest->status->label() }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted text-xs text-truncate">{{ $borrowRequest->borrower->name }}</div>
                                    <div class="text-muted text-xs mt-1">
                                        @if ($dashboardProfile['attention_mode'] === 'pending')
                                            ส่งคำขอ {{ $borrowRequest->submitted_at?->format('d/m/Y H:i') ?? '—' }} · {{ $borrowRequest->items_count }} ชิ้น
                                        @elseif ($dashboardProfile['attention_mode'] === 'operations')
                                            ยืม {{ $borrowRequest->borrow_date->format('d/m/Y') }} · คืน {{ $borrowRequest->expected_return_date->format('d/m/Y') }}
                                        @else
                                            กำหนดคืน {{ $borrowRequest->expected_return_date->format('d/m/Y') }} · {{ $borrowRequest->items_count }} ชิ้น
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </section>
</div>
@endsection

@push('vendor-scripts')
    <script src="{{ asset('template/vendor/apexcharts/apexcharts.min.js') }}"></script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const trendElement = document.getElementById('dashboard-request-trend');
        const distributionElement = document.getElementById('dashboard-distribution');

        if (!trendElement || !distributionElement || typeof ApexCharts === 'undefined') return;

        const trendData = @json($requestTrend);
        const distributionData = @json($distributionChart);
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? '#334155' : '#e2e8f0';

        new ApexCharts(trendElement, {
            chart: { type: 'area', height: 330, fontFamily: 'Anuphan, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
            series: [
                { name: 'คำขอใหม่', data: trendData.submitted },
                { name: 'คืนแล้ว', data: trendData.returned },
            ],
            colors: ['#6366f1', '#10b981'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.03, stops: [0, 92, 100] } },
            grid: { borderColor: gridColor, strokeDashArray: 5, padding: { left: 8, right: 8 } },
            xaxis: { categories: trendData.labels, axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: textColor } } },
            yaxis: { min: 0, forceNiceScale: true, labels: { style: { colors: textColor }, formatter: (value) => Number.isInteger(value) ? value : '' } },
            legend: { position: 'top', horizontalAlign: 'right', labels: { colors: textColor }, markers: { radius: 12 } },
            tooltip: { shared: true, intersect: false, theme: isDark ? 'dark' : 'light' },
            noData: { text: 'ยังไม่มีข้อมูล', style: { color: textColor } },
        }).render();

        const total = distributionData.series.reduce((sum, value) => sum + value, 0);
        const series = total > 0 ? distributionData.series : [1];
        const labels = total > 0 ? distributionData.labels : ['ยังไม่มีข้อมูล'];
        const colors = total > 0 ? distributionData.colors : ['#e2e8f0'];

        new ApexCharts(distributionElement, {
            chart: { type: 'donut', height: 330, fontFamily: 'Anuphan, sans-serif' },
            series,
            labels,
            colors,
            stroke: { width: 3, colors: [isDark ? '#1e293b' : '#ffffff'] },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '72%', labels: {
                show: true,
                name: { show: true, color: textColor, offsetY: 18 },
                value: { show: true, fontSize: '28px', fontWeight: 700, offsetY: -16 },
                total: { show: true, label: 'รวมทั้งหมด', color: textColor, formatter: () => total.toLocaleString('th-TH') },
            } } } },
            legend: { position: 'bottom', fontSize: '12px', labels: { colors: textColor }, markers: { radius: 12 }, itemMargin: { horizontal: 8, vertical: 6 } },
            tooltip: { enabled: total > 0, theme: isDark ? 'dark' : 'light' },
            responsive: [{ breakpoint: 576, options: { chart: { height: 300 } } }],
        }).render();
    });
</script>
@endpush
