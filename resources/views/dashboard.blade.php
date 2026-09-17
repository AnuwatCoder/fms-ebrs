@extends('layouts.app')

@section('title', 'แดชบอร์ด')
@section('page-title', 'แดชบอร์ด')

@section('content')
<section class="dashboard-hero card">
    <div class="dashboard-hero-orb dashboard-hero-orb-one"></div>
    <div class="dashboard-hero-orb dashboard-hero-orb-two"></div>
    <div class="dashboard-hero-content">
        <div>
            <div class="dashboard-date-chip mb-3">
                <i data-lucide="calendar-days" class="lucide-xs"></i>
                {{ now()->locale('th')->translatedFormat('l j F Y') }}
            </div>
            <h1 class="display-6 fw-bold mb-2">สวัสดี, {{ auth()->user()->name }}</h1>
            <p class="dashboard-hero-copy mb-0">
                ภาพรวมการยืม–คืนอุปกรณ์และรายการที่ต้องดำเนินการในที่เดียว
            </p>
        </div>

        <div class="dashboard-quick-actions">
            @can('borrow.create')
                <a href="{{ route('borrow.create') }}" class="dashboard-quick-link">
                    <i data-lucide="plus" class="lucide-sm"></i>
                    สร้างคำขอยืม
                </a>
            @endcan
            @can('approval.view')
                <a href="{{ route('approval.index') }}" class="dashboard-quick-link">
                    <i data-lucide="badge-check" class="lucide-sm"></i>
                    ตรวจคำขออนุมัติ
                </a>
            @endcan
            @can('checkout.view')
                <a href="{{ route('checkout.index') }}" class="dashboard-quick-link">
                    <i data-lucide="scan-line" class="lucide-sm"></i>
                    จ่ายอุปกรณ์
                </a>
            @endcan
            @can('return.view')
                <a href="{{ route('return.index') }}" class="dashboard-quick-link">
                    <i data-lucide="package-check" class="lucide-sm"></i>
                    รับคืนอุปกรณ์
                </a>
            @endcan
        </div>
    </div>
</section>

<section class="row g-4" aria-label="ตัวชี้วัดสำคัญ">
    <div class="col-sm-6 col-xl-3">
        <article class="card dashboard-metric-card dashboard-metric-primary h-100">
            <div class="dashboard-metric-header">
                <div class="dashboard-metric-icon">
                    <i data-lucide="{{ $canViewEquipment ? 'boxes' : 'clipboard-list' }}" class="lucide-md"></i>
                </div>
                <span class="dashboard-metric-label">{{ $canViewEquipment ? 'อุปกรณ์ทั้งหมด' : 'คำขอทั้งหมด' }}</span>
            </div>
            <div class="dashboard-metric-value">
                {{ number_format($canViewEquipment ? $equipmentStats['total'] : $requestStats['total']) }}
            </div>
            <div class="dashboard-metric-caption">
                {{ $canViewEquipment ? 'รายการที่เปิดใช้งานในระบบ' : 'เฉพาะคำขอที่คุณเข้าถึงได้' }}
            </div>
        </article>
    </div>

    <div class="col-sm-6 col-xl-3">
        <article class="card dashboard-metric-card dashboard-metric-success h-100">
            <div class="dashboard-metric-header">
                <div class="dashboard-metric-icon">
                    <i data-lucide="{{ $canViewEquipment ? 'circle-check-big' : 'check-check' }}" class="lucide-md"></i>
                </div>
                <span class="dashboard-metric-label">{{ $canViewEquipment ? 'พร้อมให้ยืม' : 'คืนสำเร็จ' }}</span>
            </div>
            <div class="d-flex align-items-end justify-content-between gap-3">
                <div class="dashboard-metric-value">
                    {{ number_format($canViewEquipment ? $equipmentStats['available'] : $requestStats['returned']) }}
                </div>
                @if ($canViewEquipment)
                    <span class="dashboard-rate-pill">{{ $equipmentStats['availability_rate'] }}%</span>
                @endif
            </div>
            <div class="dashboard-metric-caption">
                {{ $canViewEquipment ? 'ของอุปกรณ์ที่เปิดใช้งาน' : 'รายการที่ปิดงานเรียบร้อยแล้ว' }}
            </div>
        </article>
    </div>

    <div class="col-sm-6 col-xl-3">
        <article class="card dashboard-metric-card dashboard-metric-info h-100">
            <div class="dashboard-metric-header">
                <div class="dashboard-metric-icon">
                    <i data-lucide="activity" class="lucide-md"></i>
                </div>
                <span class="dashboard-metric-label">กำลังดำเนินการ</span>
            </div>
            <div class="dashboard-metric-value">{{ number_format($requestStats['active']) }}</div>
            <div class="dashboard-metric-caption">อนุมัติแล้ว เตรียมจ่าย หรือกำลังยืม</div>
        </article>
    </div>

    <div class="col-sm-6 col-xl-3">
        <article class="card dashboard-metric-card dashboard-metric-danger h-100">
            <div class="dashboard-metric-header">
                <div class="dashboard-metric-icon">
                    <i data-lucide="triangle-alert" class="lucide-md"></i>
                </div>
                <span class="dashboard-metric-label">เกินกำหนดคืน</span>
            </div>
            <div class="dashboard-metric-value">{{ number_format($requestStats['overdue']) }}</div>
            <div class="dashboard-metric-caption">
                {{ $requestStats['overdue'] > 0 ? 'ควรติดตามและดำเนินการโดยเร็ว' : 'ไม่มีรายการที่ต้องติดตาม' }}
            </div>
        </article>
    </div>
</section>

<section class="row g-4">
    <div class="col-xl-8">
        <article class="card dashboard-chart-card h-100">
            <div class="dashboard-card-heading">
                <div>
                    <div class="dashboard-eyebrow">แนวโน้ม 6 เดือน</div>
                    <h2 class="h5 fw-bold text-slate-800 mb-1">การยืมและการคืนอุปกรณ์</h2>
                    <p class="text-muted text-sm mb-0">เปรียบเทียบคำขอใหม่กับรายการที่คืนสำเร็จในแต่ละเดือน</p>
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
                    <h2 class="h5 fw-bold text-slate-800 mb-1">คำขอล่าสุด</h2>
                    <p class="text-muted text-sm mb-0">5 รายการล่าสุดที่คุณมีสิทธิ์เข้าถึง</p>
                </div>
                @can('borrow.view')
                    <a href="{{ route('borrow.index') }}" class="btn btn-sm btn-light d-inline-flex align-items-center gap-2">
                        ดูทั้งหมด
                        <i data-lucide="arrow-right" class="lucide-xs"></i>
                    </a>
                @elsecan('borrow.view-own')
                    <a href="{{ route('borrow.mine') }}" class="btn btn-sm btn-light d-inline-flex align-items-center gap-2">
                        ดูทั้งหมด
                        <i data-lucide="arrow-right" class="lucide-xs"></i>
                    </a>
                @endcan
            </div>

            @if ($recentRequests->isEmpty())
                <div class="dashboard-empty-state">
                    <div class="dashboard-empty-icon"><i data-lucide="inbox" class="lucide-lg"></i></div>
                    <h3 class="h6 fw-semibold mb-1">ยังไม่มีคำขอยืม</h3>
                    <p class="text-muted text-sm mb-0">เมื่อมีคำขอใหม่ รายการจะแสดงในส่วนนี้</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0" data-dashboard-table="recent-requests">
                        <thead>
                            <tr>
                                <th>เลขที่คำขอ</th>
                                <th>ผู้ยืม</th>
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
                                    <td>{{ $borrowRequest->borrower->name }}</td>
                                    <td>{{ $borrowRequest->borrow_date->format('d/m/Y') }}</td>
                                    <td class="text-center">{{ number_format($borrowRequest->items_count) }}</td>
                                    <td>
                                        <span class="badge {{ $borrowRequest->status->badgeClass() }}">
                                            {{ $borrowRequest->status->label() }}
                                        </span>
                                    </td>
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
                    <div class="dashboard-eyebrow dashboard-eyebrow-danger">ต้องติดตาม</div>
                    <h2 class="h5 fw-bold text-slate-800 mb-1">รายการเกินกำหนด</h2>
                    <p class="text-muted text-sm mb-0">เรียงจากรายการที่เกินกำหนดนานที่สุด</p>
                </div>
                <div class="dashboard-heading-icon dashboard-heading-icon-danger">
                    <i data-lucide="alarm-clock" class="lucide-md"></i>
                </div>
            </div>

            @if ($attentionRequests->isEmpty())
                <div class="dashboard-empty-state dashboard-empty-compact">
                    <div class="dashboard-empty-icon dashboard-empty-icon-success">
                        <i data-lucide="shield-check" class="lucide-lg"></i>
                    </div>
                    <h3 class="h6 fw-semibold mb-1">สถานะเรียบร้อย</h3>
                    <p class="text-muted text-sm mb-0">ไม่มีรายการยืมเกินกำหนด</p>
                </div>
            @else
                <div class="dashboard-attention-list">
                    @foreach ($attentionRequests as $borrowRequest)
                        <div class="dashboard-attention-item">
                            <div class="dashboard-attention-icon">
                                <i data-lucide="clock-3" class="lucide-sm"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                    <span class="fw-semibold text-slate-800">{{ $borrowRequest->request_no }}</span>
                                    <span class="badge badge-soft-danger">
                                        {{ $borrowRequest->expected_return_date->diffInDays(today()) }} วัน
                                    </span>
                                </div>
                                <div class="text-muted text-xs text-truncate">{{ $borrowRequest->borrower->name }}</div>
                                <div class="text-muted text-xs mt-1">
                                    กำหนดคืน {{ $borrowRequest->expected_return_date->format('d/m/Y') }} · {{ $borrowRequest->items_count }} ชิ้น
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection

@push('vendor-scripts')
    <script src="{{ asset('template/vendor/apexcharts/apexcharts.min.js') }}"></script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const trendElement = document.getElementById('dashboard-request-trend');
        const distributionElement = document.getElementById('dashboard-distribution');

        if (!trendElement || !distributionElement || typeof ApexCharts === 'undefined') {
            return;
        }

        const trendData = @json($requestTrend);
        const distributionData = @json($distributionChart);
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? '#334155' : '#e2e8f0';

        const trendChart = new ApexCharts(trendElement, {
            chart: {
                type: 'area',
                height: 330,
                fontFamily: 'Anuphan, sans-serif',
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            series: [
                { name: 'คำขอใหม่', data: trendData.submitted },
                { name: 'คืนแล้ว', data: trendData.returned },
            ],
            colors: ['#6366f1', '#10b981'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: { opacityFrom: 0.3, opacityTo: 0.03, stops: [0, 92, 100] },
            },
            grid: { borderColor: gridColor, strokeDashArray: 5, padding: { left: 8, right: 8 } },
            xaxis: {
                categories: trendData.labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: textColor } },
            },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: {
                    style: { colors: textColor },
                    formatter: (value) => Number.isInteger(value) ? value : '',
                },
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                labels: { colors: textColor },
                markers: { radius: 12 },
            },
            tooltip: { shared: true, intersect: false, theme: isDark ? 'dark' : 'light' },
            noData: { text: 'ยังไม่มีข้อมูล', style: { color: textColor } },
        });

        const distributionTotal = distributionData.series.reduce((total, value) => total + value, 0);
        const distributionSeries = distributionTotal > 0 ? distributionData.series : [1];
        const distributionLabels = distributionTotal > 0 ? distributionData.labels : ['ยังไม่มีข้อมูล'];
        const distributionColors = distributionTotal > 0 ? distributionData.colors : ['#e2e8f0'];

        const distributionChart = new ApexCharts(distributionElement, {
            chart: {
                type: 'donut',
                height: 330,
                fontFamily: 'Anuphan, sans-serif',
            },
            series: distributionSeries,
            labels: distributionLabels,
            colors: distributionColors,
            stroke: { width: 3, colors: [isDark ? '#1e293b' : '#ffffff'] },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: true, color: textColor, offsetY: 18 },
                            value: { show: true, fontSize: '28px', fontWeight: 700, offsetY: -16 },
                            total: {
                                show: true,
                                label: 'รวมทั้งหมด',
                                color: textColor,
                                formatter: () => distributionTotal.toLocaleString('th-TH'),
                            },
                        },
                    },
                },
            },
            legend: {
                position: 'bottom',
                fontSize: '12px',
                labels: { colors: textColor },
                markers: { radius: 12 },
                itemMargin: { horizontal: 8, vertical: 6 },
            },
            tooltip: { enabled: distributionTotal > 0, theme: isDark ? 'dark' : 'light' },
            responsive: [{ breakpoint: 576, options: { chart: { height: 300 } } }],
        });

        trendChart.render();
        distributionChart.render();
    });
</script>
@endpush
