<?php

namespace App\Services\Dashboard;

use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\User;
use App\Support\Authorization\CoreRoles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardQueryService
{
    /** @return array<string, mixed> */
    public function for(User $user): array
    {
        $role = $this->dashboardRole($user);
        $showEquipment = $user->can('equipment.view')
            && in_array($role, ['SuperAdmin', 'Admin', 'Staff'], true);
        $equipmentStats = $this->equipmentStats($showEquipment);
        $userStats = $this->userStats($role === 'SuperAdmin' && $user->can('user.manage'));
        $requests = $this->visibleBorrowRequests($user);
        $counts = (clone $requests)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $requestStats = $this->requestStats($counts, clone $requests);
        $profile = $this->profile($user, $role, $requestStats, $equipmentStats, $userStats);

        return [
            'dashboardProfile' => $profile,
            'canViewEquipment' => $showEquipment,
            'equipmentStats' => $equipmentStats,
            'userStats' => $userStats,
            'requestStats' => $requestStats,
            'requestTrend' => $this->requestTrend(clone $requests),
            'distributionChart' => $showEquipment
                ? $this->equipmentDistribution($equipmentStats)
                : $this->requestDistribution($counts, $role === 'Borrower'),
            'recentRequests' => (clone $requests)
                ->with('borrower:id,name')
                ->withCount('items')
                ->latest()
                ->limit(5)
                ->get(),
            'attentionRequests' => $this->attentionRequests(clone $requests, $profile['attention_mode']),
        ];
    }

    private function dashboardRole(User $user): string
    {
        $roleNames = $user->getRoleNames();

        foreach (CoreRoles::NAMES as $roleName) {
            if ($roleNames->contains($roleName)) {
                return $roleName;
            }
        }

        return (string) ($roleNames->first() ?? 'User');
    }

    /**
     * @param  Collection<string, int|string>  $counts
     * @return array<string, int>
     */
    private function requestStats(Collection $counts, Builder $requests): array
    {
        $stats = [
            'total' => (int) $counts->sum(),
            'pending' => $this->countFor($counts, BorrowRequestStatus::Pending),
            'approved' => $this->countFor($counts, BorrowRequestStatus::Approved),
            'ready_for_pickup' => $this->countFor($counts, BorrowRequestStatus::ReadyForPickup),
            'borrowed' => $this->countFor($counts, BorrowRequestStatus::Borrowed),
            'returned' => $this->countFor($counts, BorrowRequestStatus::Returned),
            'rejected' => $this->countFor($counts, BorrowRequestStatus::Rejected),
            'cancelled' => $this->countFor($counts, BorrowRequestStatus::Cancelled),
            'overdue_status' => $this->countFor($counts, BorrowRequestStatus::Overdue),
            'overdue' => $this->overdue($requests)->count(),
        ];
        $stats['active'] = $stats['approved'] + $stats['ready_for_pickup']
            + $stats['borrowed'] + $stats['overdue_status'];

        return $stats;
    }

    /**
     * @param  array<string, int>  $requests
     * @param  array<string, int|null>  $equipment
     * @param  array<string, int|null>  $users
     * @return array<string, mixed>
     */
    private function profile(User $user, string $role, array $requests, array $equipment, array $users): array
    {
        $profile = match ($role) {
            'Borrower' => [
                'key' => 'borrower',
                'hero_title' => 'ติดตามการยืมของคุณได้ในที่เดียว',
                'hero_description' => 'ตรวจสอบสถานะคำขอ วันรับ–คืน และรายการที่ต้องดำเนินการของคุณ',
                'metrics' => [
                    $this->metric('คำขอของฉัน', $requests['total'], 'คำขอทั้งหมดที่คุณส่ง', 'clipboard-list', 'primary'),
                    $this->metric('รออนุมัติ', $requests['pending'], 'อยู่ระหว่างการพิจารณา', 'hourglass', 'warning'),
                    $this->metric('กำลังดำเนินการ', $requests['active'], 'อนุมัติแล้วหรือกำลังยืม', 'activity', 'info'),
                    $this->metric('เกินกำหนดคืน', $requests['overdue'], 'รายการที่ควรดำเนินการโดยเร็ว', 'triangle-alert', 'danger'),
                ],
                'trend_title' => 'แนวโน้มคำขอของฉัน',
                'trend_description' => 'เปรียบเทียบคำขอใหม่และรายการที่คืนสำเร็จในแต่ละเดือน',
                'recent_title' => 'คำขอล่าสุดของฉัน',
                'recent_description' => '5 คำขอล่าสุดจากบัญชีของคุณ',
                'empty_recent' => 'เริ่มสร้างคำขอยืม แล้วรายการล่าสุดจะแสดงที่นี่',
                'attention_mode' => 'overdue',
                'attention_title' => 'คำขอที่ต้องติดตาม',
                'attention_description' => 'รายการของคุณที่เกินกำหนดคืน',
                'empty_attention' => 'ไม่มีคำขอที่เกินกำหนดคืน',
                'attention_icon' => 'clock-alert',
                'show_borrower' => false,
            ],
            'Approver' => [
                'key' => 'approver',
                'hero_title' => 'พิจารณาคำขอได้อย่างรวดเร็วและชัดเจน',
                'hero_description' => 'ดูคำขอที่รออนุมัติ ตรวจสอบภาระงาน และติดตามผลการพิจารณา',
                'metrics' => [
                    $this->metric('รอพิจารณา', $requests['pending'], 'คำขอที่ยังไม่ได้ดำเนินการ', 'inbox', 'warning'),
                    $this->metric('อนุมัติแล้ว', $requests['approved'] + $requests['ready_for_pickup'], 'คำขอที่ผ่านการอนุมัติ', 'badge-check', 'success'),
                    $this->metric('ไม่อนุมัติ', $requests['rejected'], 'คำขอที่ปิดด้วยการปฏิเสธ', 'circle-x', 'danger'),
                    $this->metric('รายการเกินกำหนด', $requests['overdue'], 'ข้อมูลประกอบการติดตาม', 'alarm-clock', 'info'),
                ],
                'trend_title' => 'แนวโน้มคำขอเข้าสู่ระบบ',
                'trend_description' => 'ภาพรวมคำขอใหม่และรายการที่คืนสำเร็จในช่วง 6 เดือน',
                'recent_title' => 'คำขอล่าสุด',
                'recent_description' => 'คำขอล่าสุดที่อยู่ในขอบเขตการพิจารณา',
                'empty_recent' => 'เมื่อมีคำขอใหม่ รายการจะแสดงที่นี่',
                'attention_mode' => 'pending',
                'attention_title' => 'คำขอรออนุมัติ',
                'attention_description' => 'เรียงจากคำขอที่รอนานที่สุด',
                'empty_attention' => 'ไม่มีคำขอรอการอนุมัติ',
                'attention_icon' => 'clipboard-check',
                'show_borrower' => true,
            ],
            'Staff' => [
                'key' => 'staff',
                'hero_title' => 'บริหารคิวจ่ายและรับคืนอุปกรณ์',
                'hero_description' => 'เตรียมอุปกรณ์ ตรวจรับคืน และจัดการรายการที่ต้องดูแลจากหน้าปฏิบัติงานเดียว',
                'metrics' => [
                    $this->metric('รอจ่ายอุปกรณ์', $requests['approved'] + $requests['ready_for_pickup'], 'อนุมัติแล้วและพร้อมดำเนินการ', 'scan-line', 'warning'),
                    $this->metric('กำลังถูกยืม', $requests['borrowed'], 'รายการที่ยังไม่คืนอุปกรณ์', 'package-open', 'primary'),
                    $this->metric('เกินกำหนดคืน', $requests['overdue'], 'ควรติดตามผู้ยืมโดยเร็ว', 'alarm-clock', 'danger'),
                    $this->metric('อุปกรณ์ต้องดูแล', (int) $equipment['attention'], 'ซ่อมบำรุง ชำรุด หรือสูญหาย', 'wrench', 'info'),
                ],
                'trend_title' => 'ปริมาณงานยืมและคืน',
                'trend_description' => 'แนวโน้มคำขอใหม่เทียบกับงานรับคืนที่เสร็จสิ้น',
                'recent_title' => 'รายการยืมล่าสุด',
                'recent_description' => 'รายการล่าสุดสำหรับเตรียมงานจ่ายและรับคืน',
                'empty_recent' => 'ยังไม่มีรายการยืมในระบบ',
                'attention_mode' => 'operations',
                'attention_title' => 'คิวงานที่ต้องดำเนินการ',
                'attention_description' => 'รายการรอจ่าย กำลังยืม หรือเกินกำหนด',
                'empty_attention' => 'ไม่มีคิวงานที่ต้องดำเนินการ',
                'attention_icon' => 'list-checks',
                'show_borrower' => true,
            ],
            'Admin' => [
                'key' => 'admin',
                'hero_title' => 'ภาพรวมการดำเนินงานและทรัพยากร',
                'hero_description' => 'ติดตามอุปกรณ์ คำขอยืม และรายการผิดปกติ เพื่อบริหารงานได้อย่างต่อเนื่อง',
                'metrics' => [
                    $this->metric('อุปกรณ์ทั้งหมด', (int) $equipment['total'], 'รายการที่เปิดใช้งานในระบบ', 'boxes', 'primary'),
                    $this->metric('พร้อมให้ยืม', (int) $equipment['available'], $equipment['availability_rate'].'% ของอุปกรณ์ทั้งหมด', 'circle-check-big', 'success'),
                    $this->metric('คำขอรอดำเนินการ', $requests['pending'], 'คำขอที่ยังรอการพิจารณา', 'clipboard-clock', 'warning'),
                    $this->metric('อุปกรณ์ต้องดูแล', (int) $equipment['attention'], 'ซ่อมบำรุง ชำรุด หรือสูญหาย', 'triangle-alert', 'danger'),
                ],
                'trend_title' => 'ภาพรวมการยืมและคืน',
                'trend_description' => 'แนวโน้มการใช้งานอุปกรณ์ในช่วง 6 เดือนล่าสุด',
                'recent_title' => 'กิจกรรมคำขอล่าสุด',
                'recent_description' => '5 รายการล่าสุดจากผู้ใช้งานทั้งหมด',
                'empty_recent' => 'ยังไม่มีคำขอยืมในระบบ',
                'attention_mode' => 'overdue',
                'attention_title' => 'รายการเกินกำหนด',
                'attention_description' => 'เรียงจากรายการที่เกินกำหนดนานที่สุด',
                'empty_attention' => 'ไม่มีรายการยืมเกินกำหนด',
                'attention_icon' => 'alarm-clock',
                'show_borrower' => true,
            ],
            'SuperAdmin' => [
                'key' => 'superadmin',
                'hero_title' => 'ศูนย์ควบคุมระบบ FMS EBRS',
                'hero_description' => 'กำกับดูแลผู้ใช้งาน สิทธิ์ ระบบงาน และภาพรวมทรัพยากรจากมุมมองสูงสุด',
                'metrics' => [
                    $this->metric('ผู้ใช้งานที่เปิดใช้', (int) $users['active'], 'จากทั้งหมด '.number_format((int) $users['total']).' คน', 'users-round', 'violet'),
                    $this->metric('อุปกรณ์ทั้งหมด', (int) $equipment['total'], 'รายการที่เปิดใช้งานในระบบ', 'boxes', 'primary'),
                    $this->metric('คำขอกำลังดำเนินการ', $requests['active'], 'อนุมัติ เตรียมจ่าย หรือกำลังยืม', 'activity', 'info'),
                    $this->metric('รายการเกินกำหนด', $requests['overdue'], 'รายการที่ต้องติดตามทั้งระบบ', 'triangle-alert', 'danger'),
                ],
                'trend_title' => 'ภาพรวมประสิทธิภาพระบบ',
                'trend_description' => 'แนวโน้มคำขอและการคืนอุปกรณ์ของทั้งองค์กร',
                'recent_title' => 'กิจกรรมล่าสุดในระบบ',
                'recent_description' => 'คำขอล่าสุดจากผู้ใช้งานทุกบทบาท',
                'empty_recent' => 'ยังไม่มีกิจกรรมคำขอยืมในระบบ',
                'attention_mode' => 'overdue',
                'attention_title' => 'รายการที่ต้องกำกับติดตาม',
                'attention_description' => 'คำขอเกินกำหนดที่ควรได้รับการติดตาม',
                'empty_attention' => 'ไม่มีรายการที่ต้องกำกับติดตาม',
                'attention_icon' => 'shield-alert',
                'show_borrower' => true,
            ],
            default => $this->customProfile($user, $requests),
        };

        return [
            ...$profile,
            'role' => $role,
            'role_label' => CoreRoles::label($role),
            'actions' => $this->actions($user, $role),
            'recent_route' => $user->can('borrow.view')
                ? 'borrow.index'
                : ($user->can('borrow.view-own') ? 'borrow.mine' : null),
        ];
    }

    /** @param array<string, int> $requests */
    private function customProfile(User $user, array $requests): array
    {
        return [
            'key' => 'custom',
            'hero_title' => 'ภาพรวมงานของคุณ',
            'hero_description' => 'ข้อมูลและทางลัดจะแสดงตามสิทธิ์ที่ได้รับมอบหมาย',
            'metrics' => [
                $this->metric('คำขอทั้งหมด', $requests['total'], 'รายการที่คุณมีสิทธิ์เข้าถึง', 'clipboard-list', 'primary'),
                $this->metric('รอดำเนินการ', $requests['pending'], 'คำขอที่ยังอยู่ระหว่างดำเนินการ', 'hourglass', 'warning'),
                $this->metric('กำลังดำเนินการ', $requests['active'], 'รายการที่อยู่ใน workflow', 'activity', 'info'),
                $this->metric('เกินกำหนดคืน', $requests['overdue'], 'รายการที่ควรติดตาม', 'triangle-alert', 'danger'),
            ],
            'trend_title' => 'แนวโน้มคำขอ',
            'trend_description' => 'ข้อมูลตามขอบเขตสิทธิ์ของคุณในช่วง 6 เดือน',
            'recent_title' => 'คำขอล่าสุด',
            'recent_description' => 'รายการล่าสุดที่คุณมีสิทธิ์เข้าถึง',
            'empty_recent' => 'ยังไม่มีคำขอที่คุณสามารถเข้าถึงได้',
            'attention_mode' => 'overdue',
            'attention_title' => 'รายการที่ต้องติดตาม',
            'attention_description' => 'รายการเกินกำหนดตามขอบเขตสิทธิ์',
            'empty_attention' => 'ไม่มีรายการที่ต้องติดตาม',
            'attention_icon' => 'bell-ring',
            'show_borrower' => $user->can('borrow.view'),
        ];
    }

    /** @return array{label: string, value: int, caption: string, icon: string, tone: string} */
    private function metric(string $label, int $value, string $caption, string $icon, string $tone): array
    {
        return compact('label', 'value', 'caption', 'icon', 'tone');
    }

    /** @return list<array{label: string, icon: string, route: string, permission: string}> */
    private function actions(User $user, string $role): array
    {
        $actions = match ($role) {
            'Borrower' => [
                ['label' => 'สร้างคำขอยืม', 'icon' => 'plus', 'route' => 'borrow.create', 'permission' => 'borrow.create'],
                ['label' => 'คำขอของฉัน', 'icon' => 'clipboard-list', 'route' => 'borrow.mine', 'permission' => 'borrow.view-own'],
                ['label' => 'ค้นหาอุปกรณ์', 'icon' => 'search', 'route' => 'equipment.index', 'permission' => 'equipment.view'],
            ],
            'Approver' => [
                ['label' => 'ตรวจคำขออนุมัติ', 'icon' => 'badge-check', 'route' => 'approval.index', 'permission' => 'approval.view'],
                ['label' => 'คำขอทั้งหมด', 'icon' => 'clipboard-list', 'route' => 'borrow.index', 'permission' => 'borrow.view'],
            ],
            'Staff' => [
                ['label' => 'จ่ายอุปกรณ์', 'icon' => 'scan-line', 'route' => 'checkout.index', 'permission' => 'checkout.view'],
                ['label' => 'รับคืนอุปกรณ์', 'icon' => 'package-check', 'route' => 'return.index', 'permission' => 'return.view'],
                ['label' => 'งานซ่อมบำรุง', 'icon' => 'wrench', 'route' => 'maintenance.index', 'permission' => 'maintenance.view'],
                ['label' => 'รายการอุปกรณ์', 'icon' => 'boxes', 'route' => 'equipment.index', 'permission' => 'equipment.view'],
            ],
            'Admin' => [
                ['label' => 'คำขอทั้งหมด', 'icon' => 'clipboard-list', 'route' => 'borrow.index', 'permission' => 'borrow.view'],
                ['label' => 'จ่ายอุปกรณ์', 'icon' => 'scan-line', 'route' => 'checkout.index', 'permission' => 'checkout.view'],
                ['label' => 'รับคืนอุปกรณ์', 'icon' => 'package-check', 'route' => 'return.index', 'permission' => 'return.view'],
                ['label' => 'งานซ่อมบำรุง', 'icon' => 'wrench', 'route' => 'maintenance.index', 'permission' => 'maintenance.view'],
            ],
            'SuperAdmin' => [
                ['label' => 'จัดการผู้ใช้งาน', 'icon' => 'users-round', 'route' => 'admin.users', 'permission' => 'user.manage'],
                ['label' => 'บทบาทและสิทธิ์', 'icon' => 'shield-check', 'route' => 'admin.roles', 'permission' => 'role.manage'],
                ['label' => 'ตั้งค่าระบบ', 'icon' => 'settings', 'route' => 'admin.settings', 'permission' => 'settings.manage'],
                ['label' => 'Audit Log', 'icon' => 'history', 'route' => 'audit.index', 'permission' => 'audit.view'],
            ],
            default => [
                ['label' => 'สร้างคำขอยืม', 'icon' => 'plus', 'route' => 'borrow.create', 'permission' => 'borrow.create'],
                ['label' => 'ตรวจคำขออนุมัติ', 'icon' => 'badge-check', 'route' => 'approval.index', 'permission' => 'approval.view'],
                ['label' => 'จ่ายอุปกรณ์', 'icon' => 'scan-line', 'route' => 'checkout.index', 'permission' => 'checkout.view'],
                ['label' => 'รับคืนอุปกรณ์', 'icon' => 'package-check', 'route' => 'return.index', 'permission' => 'return.view'],
                ['label' => 'รายการอุปกรณ์', 'icon' => 'boxes', 'route' => 'equipment.index', 'permission' => 'equipment.view'],
            ],
        };

        return array_values(array_filter($actions, fn (array $action): bool => $user->can($action['permission'])));
    }

    /** @return Builder<BorrowRequest> */
    private function visibleBorrowRequests(User $user): Builder
    {
        return BorrowRequest::query()
            ->when(! $user->can('borrow.view'), fn (Builder $query): Builder => $query->where('user_id', $user->id));
    }

    /** @return array<string, int|null> */
    private function equipmentStats(bool $include): array
    {
        if (! $include) {
            return ['total' => null, 'available' => null, 'reserved' => null, 'borrowed' => null, 'attention' => null, 'availability_rate' => null];
        }

        $counts = Equipment::query()
            ->where('active', true)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $total = (int) $counts->sum();
        $available = (int) ($counts[EquipmentStatus::Available->value] ?? 0);

        return [
            'total' => $total,
            'available' => $available,
            'reserved' => (int) ($counts[EquipmentStatus::Reserved->value] ?? 0),
            'borrowed' => (int) ($counts[EquipmentStatus::Borrowed->value] ?? 0),
            'attention' => (int) ($counts[EquipmentStatus::Maintenance->value] ?? 0)
                + (int) ($counts[EquipmentStatus::Damaged->value] ?? 0)
                + (int) ($counts[EquipmentStatus::Lost->value] ?? 0),
            'availability_rate' => $total > 0 ? (int) round(($available / $total) * 100) : 0,
        ];
    }

    /** @return array{total: int|null, active: int|null} */
    private function userStats(bool $include): array
    {
        return $include
            ? ['total' => User::query()->count(), 'active' => User::query()->where('active', true)->count()]
            : ['total' => null, 'active' => null];
    }

    /** @param Collection<string, int|string> $counts */
    private function countFor(Collection $counts, BorrowRequestStatus $status): int
    {
        return (int) ($counts[$status->value] ?? 0);
    }

    /** @return array{labels: list<string>, submitted: list<int>, returned: list<int>} */
    private function requestTrend(Builder $query): array
    {
        $months = collect(range(5, 0))->map(fn (int $ago): Carbon => today()->startOfMonth()->subMonths($ago));
        $submitted = array_fill_keys($months->map->format('Y-m')->all(), 0);
        $returned = $submitted;
        $firstMonth = $months->first();

        (clone $query)->where('created_at', '>=', $firstMonth)->select(['id', 'created_at'])->lazyById()
            ->each(function (BorrowRequest $request) use (&$submitted): void {
                $key = $request->created_at->format('Y-m');
                if (array_key_exists($key, $submitted)) {
                    $submitted[$key]++;
                }
            });
        (clone $query)->where('status', BorrowRequestStatus::Returned->value)
            ->where('updated_at', '>=', $firstMonth)->select(['id', 'updated_at'])->lazyById()
            ->each(function (BorrowRequest $request) use (&$returned): void {
                $key = $request->updated_at->format('Y-m');
                if (array_key_exists($key, $returned)) {
                    $returned[$key]++;
                }
            });

        return [
            'labels' => $months->map(fn (Carbon $month): string => $month->locale('th')->translatedFormat('M y'))->values()->all(),
            'submitted' => array_values($submitted),
            'returned' => array_values($returned),
        ];
    }

    /** @param array<string, int|null> $stats */
    private function equipmentDistribution(array $stats): array
    {
        return [
            'title' => 'สถานะอุปกรณ์',
            'subtitle' => 'ภาพรวมอุปกรณ์ที่เปิดใช้งาน',
            'labels' => ['พร้อมให้ยืม', 'จองแล้ว', 'กำลังถูกยืม', 'ต้องดูแล'],
            'series' => [(int) $stats['available'], (int) $stats['reserved'], (int) $stats['borrowed'], (int) $stats['attention']],
            'colors' => ['#10b981', '#38bdf8', '#6366f1', '#f59e0b'],
        ];
    }

    /** @param Collection<string, int|string> $counts */
    private function requestDistribution(Collection $counts, bool $mine): array
    {
        return [
            'title' => $mine ? 'สถานะคำขอของฉัน' : 'สถานะคำขอยืม',
            'subtitle' => $mine ? 'สัดส่วนคำขอทั้งหมดของคุณ' : 'สัดส่วนคำขอตามสถานะในระบบ',
            'labels' => ['รออนุมัติ', 'กำลังเตรียม', 'กำลังยืม', 'คืนแล้ว', 'ปิดรายการ'],
            'series' => [
                $this->countFor($counts, BorrowRequestStatus::Pending),
                $this->countFor($counts, BorrowRequestStatus::Approved) + $this->countFor($counts, BorrowRequestStatus::ReadyForPickup),
                $this->countFor($counts, BorrowRequestStatus::Borrowed) + $this->countFor($counts, BorrowRequestStatus::Overdue),
                $this->countFor($counts, BorrowRequestStatus::Returned),
                $this->countFor($counts, BorrowRequestStatus::Rejected) + $this->countFor($counts, BorrowRequestStatus::Cancelled),
            ],
            'colors' => ['#f59e0b', '#38bdf8', '#6366f1', '#10b981', '#94a3b8'],
        ];
    }

    /** @return Collection<int, BorrowRequest> */
    private function attentionRequests(Builder $query, string $mode): Collection
    {
        $query->with('borrower:id,name')->withCount('items')->limit(5);

        return match ($mode) {
            'pending' => $query->where('status', BorrowRequestStatus::Pending->value)->oldest('submitted_at')->get(),
            'operations' => $query->whereIn('status', [
                BorrowRequestStatus::Approved->value,
                BorrowRequestStatus::ReadyForPickup->value,
                BorrowRequestStatus::Borrowed->value,
                BorrowRequestStatus::Overdue->value,
            ])->orderBy('borrow_date')->get(),
            default => $this->overdue($query)->orderBy('expected_return_date')->get(),
        };
    }

    /** @param Builder<BorrowRequest> $query */
    private function overdue(Builder $query): Builder
    {
        return $query
            ->whereDate('expected_return_date', '<', today())
            ->whereIn('status', [
                BorrowRequestStatus::Approved->value,
                BorrowRequestStatus::ReadyForPickup->value,
                BorrowRequestStatus::Borrowed->value,
                BorrowRequestStatus::Overdue->value,
            ]);
    }
}
