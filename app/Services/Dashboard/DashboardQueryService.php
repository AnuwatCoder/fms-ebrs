<?php

namespace App\Services\Dashboard;

use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Models\BorrowRequest;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardQueryService
{
    /** @return array<string, mixed> */
    public function for(User $user): array
    {
        $canViewEquipment = $user->can('equipment.view');
        $equipmentStats = $this->equipmentStats($canViewEquipment);
        $borrowRequests = $this->visibleBorrowRequests($user);
        $requestCounts = (clone $borrowRequests)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $requestStats = [
            'total' => (int) $requestCounts->sum(),
            'pending' => $this->countFor($requestCounts, BorrowRequestStatus::Pending),
            'active' => $this->countFor($requestCounts, BorrowRequestStatus::Approved)
                + $this->countFor($requestCounts, BorrowRequestStatus::ReadyForPickup)
                + $this->countFor($requestCounts, BorrowRequestStatus::Borrowed)
                + $this->countFor($requestCounts, BorrowRequestStatus::Overdue),
            'returned' => $this->countFor($requestCounts, BorrowRequestStatus::Returned),
            'overdue' => $this->overdue(clone $borrowRequests)->count(),
        ];

        return [
            'canViewEquipment' => $canViewEquipment,
            'equipmentStats' => $equipmentStats,
            'requestStats' => $requestStats,
            'requestTrend' => $this->requestTrend(clone $borrowRequests),
            'distributionChart' => $canViewEquipment
                ? $this->equipmentDistribution($equipmentStats)
                : $this->requestDistribution($requestCounts),
            'recentRequests' => (clone $borrowRequests)
                ->with('borrower:id,name')
                ->withCount('items')
                ->latest()
                ->limit(5)
                ->get(),
            'attentionRequests' => $this->overdue(clone $borrowRequests)
                ->with('borrower:id,name')
                ->withCount('items')
                ->orderBy('expected_return_date')
                ->limit(5)
                ->get(),
        ];
    }

    /** @return Builder<BorrowRequest> */
    private function visibleBorrowRequests(User $user): Builder
    {
        return BorrowRequest::query()
            ->when(! $user->can('borrow.view'), fn (Builder $query): Builder => $query->where('user_id', $user->id));
    }

    /**
     * @return array{
     *     total: int|null,
     *     available: int|null,
     *     reserved: int|null,
     *     borrowed: int|null,
     *     attention: int|null,
     *     availability_rate: int|null
     * }
     */
    private function equipmentStats(bool $canViewEquipment): array
    {
        if (! $canViewEquipment) {
            return [
                'total' => null,
                'available' => null,
                'reserved' => null,
                'borrowed' => null,
                'attention' => null,
                'availability_rate' => null,
            ];
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

    /**
     * @param  Collection<string, int|string>  $counts
     */
    private function countFor(Collection $counts, BorrowRequestStatus $status): int
    {
        return (int) ($counts[$status->value] ?? 0);
    }

    /** @return array{labels: list<string>, submitted: list<int>, returned: list<int>} */
    private function requestTrend(Builder $query): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo): Carbon => today()->startOfMonth()->subMonths($monthsAgo));
        $submitted = array_fill_keys($months->map->format('Y-m')->all(), 0);
        $returned = $submitted;
        $firstMonth = $months->first();

        (clone $query)
            ->where('created_at', '>=', $firstMonth)
            ->select(['id', 'created_at'])
            ->lazyById()
            ->each(function (BorrowRequest $borrowRequest) use (&$submitted): void {
                $key = $borrowRequest->created_at->format('Y-m');

                if (array_key_exists($key, $submitted)) {
                    $submitted[$key]++;
                }
            });

        (clone $query)
            ->where('status', BorrowRequestStatus::Returned->value)
            ->where('updated_at', '>=', $firstMonth)
            ->select(['id', 'updated_at'])
            ->lazyById()
            ->each(function (BorrowRequest $borrowRequest) use (&$returned): void {
                $key = $borrowRequest->updated_at->format('Y-m');

                if (array_key_exists($key, $returned)) {
                    $returned[$key]++;
                }
            });

        return [
            'labels' => $months
                ->map(fn (Carbon $month): string => $month->locale('th')->translatedFormat('M y'))
                ->values()
                ->all(),
            'submitted' => array_values($submitted),
            'returned' => array_values($returned),
        ];
    }

    /**
     * @param  array{total: int|null, available: int|null, reserved: int|null, borrowed: int|null, attention: int|null, availability_rate: int|null}  $stats
     * @return array{title: string, subtitle: string, labels: list<string>, series: list<int>, colors: list<string>}
     */
    private function equipmentDistribution(array $stats): array
    {
        return [
            'title' => 'สถานะอุปกรณ์',
            'subtitle' => 'ภาพรวมอุปกรณ์ที่เปิดใช้งาน',
            'labels' => ['พร้อมให้ยืม', 'จองแล้ว', 'กำลังถูกยืม', 'ต้องดูแล'],
            'series' => [
                (int) $stats['available'],
                (int) $stats['reserved'],
                (int) $stats['borrowed'],
                (int) $stats['attention'],
            ],
            'colors' => ['#10b981', '#38bdf8', '#6366f1', '#f59e0b'],
        ];
    }

    /**
     * @param  Collection<string, int|string>  $counts
     * @return array{title: string, subtitle: string, labels: list<string>, series: list<int>, colors: list<string>}
     */
    private function requestDistribution(Collection $counts): array
    {
        return [
            'title' => 'สถานะคำขอของฉัน',
            'subtitle' => 'สัดส่วนคำขอทั้งหมดที่คุณเข้าถึงได้',
            'labels' => ['รออนุมัติ', 'กำลังเตรียม', 'กำลังยืม', 'คืนแล้ว', 'ปิดรายการ'],
            'series' => [
                $this->countFor($counts, BorrowRequestStatus::Pending),
                $this->countFor($counts, BorrowRequestStatus::Approved)
                    + $this->countFor($counts, BorrowRequestStatus::ReadyForPickup),
                $this->countFor($counts, BorrowRequestStatus::Borrowed)
                    + $this->countFor($counts, BorrowRequestStatus::Overdue),
                $this->countFor($counts, BorrowRequestStatus::Returned),
                $this->countFor($counts, BorrowRequestStatus::Rejected)
                    + $this->countFor($counts, BorrowRequestStatus::Cancelled),
            ],
            'colors' => ['#f59e0b', '#38bdf8', '#6366f1', '#10b981', '#94a3b8'],
        ];
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
