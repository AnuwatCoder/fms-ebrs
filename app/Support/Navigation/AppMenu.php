<?php

namespace App\Support\Navigation;

use App\Models\User;

final class AppMenu
{
    /**
     * Build the application navigation that the authenticated user is allowed to see.
     *
     * @return list<array{
     *     key: string,
     *     label: string,
     *     items: list<array{
     *         key: string,
     *         label: string,
     *         icon: string,
     *         abilities: list<string>,
     *         route?: string,
     *         active?: string
     *     }>
     * }>
     */
    public function for(User $user): array
    {
        $groups = [
            [
                'key' => 'overview',
                'label' => 'ภาพรวม',
                'items' => [
                    [
                        'key' => 'dashboard',
                        'label' => 'แดชบอร์ด',
                        'icon' => 'layout-dashboard',
                        'abilities' => [],
                        'route' => 'dashboard',
                        'active' => 'dashboard',
                    ],
                ],
            ],
            [
                'key' => 'equipment',
                'label' => 'อุปกรณ์',
                'items' => [
                    [
                        'key' => 'equipment.index',
                        'label' => 'รายการอุปกรณ์',
                        'icon' => 'monitor',
                        'abilities' => ['equipment.view'],
                        'route' => 'equipment.index',
                        'active' => 'equipment.*',
                    ],
                    [
                        'key' => 'category.index',
                        'label' => 'หมวดหมู่อุปกรณ์',
                        'icon' => 'tags',
                        'abilities' => ['category.view'],
                        'route' => 'category.index',
                        'active' => 'category.*',
                    ],
                    [
                        'key' => 'maintenance.index',
                        'label' => 'บำรุงรักษาและเหตุขัดข้อง',
                        'icon' => 'wrench',
                        'abilities' => ['maintenance.view'],
                        'route' => 'maintenance.index',
                        'active' => 'maintenance.*',
                    ],
                ],
            ],
            [
                'key' => 'borrowing',
                'label' => 'การยืม',
                'items' => [
                    [
                        'key' => 'borrow.create',
                        'label' => 'สร้างคำขอยืม',
                        'icon' => 'circle-plus',
                        'abilities' => ['borrow.create'],
                        'route' => 'borrow.create',
                        'active' => 'borrow.create',
                    ],
                    [
                        'key' => 'borrow.mine',
                        'label' => 'คำขอยืมของฉัน',
                        'icon' => 'clipboard-check',
                        'abilities' => ['borrow.view-own'],
                        'route' => 'borrow.mine',
                        'active' => 'borrow.mine',
                    ],
                    [
                        'key' => 'borrow.calendar',
                        'label' => 'ปฏิทินการจอง',
                        'icon' => 'calendar-days',
                        'abilities' => ['equipment.view'],
                        'route' => 'borrow.calendar',
                        'active' => 'borrow.calendar',
                    ],
                    [
                        'key' => 'borrow.index',
                        'label' => 'คำขอยืมทั้งหมด',
                        'icon' => 'clipboard-list',
                        'abilities' => ['borrow.view'],
                        'route' => 'borrow.index',
                        'active' => 'borrow.index',
                    ],
                ],
            ],
            [
                'key' => 'operations',
                'label' => 'อนุมัติและปฏิบัติการ',
                'items' => [
                    [
                        'key' => 'approval.index',
                        'label' => 'คำขอรออนุมัติ',
                        'icon' => 'badge-check',
                        'abilities' => ['approval.view'],
                        'route' => 'approval.index',
                        'active' => 'approval.*',
                    ],
                    [
                        'key' => 'checkout.index',
                        'label' => 'จ่ายอุปกรณ์',
                        'icon' => 'scan-line',
                        'abilities' => ['checkout.view'],
                        'route' => 'checkout.index',
                        'active' => 'checkout.*',
                    ],
                    [
                        'key' => 'return.index',
                        'label' => 'รับคืนอุปกรณ์',
                        'icon' => 'package-check',
                        'abilities' => ['return.view'],
                        'route' => 'return.index',
                        'active' => 'return.*',
                    ],
                ],
            ],
            [
                'key' => 'reports',
                'label' => 'รายงาน',
                'items' => [
                    [
                        'key' => 'report.equipment',
                        'label' => 'รายงานอุปกรณ์',
                        'icon' => 'monitor-cog',
                        'abilities' => ['report.equipment'],
                        'route' => 'report.equipment',
                        'active' => 'report.equipment',
                    ],
                    [
                        'key' => 'report.borrowing',
                        'label' => 'รายงานการยืม',
                        'icon' => 'chart-no-axes-combined',
                        'abilities' => ['report.borrowing'],
                        'route' => 'report.borrowing',
                        'active' => 'report.borrowing',
                    ],
                    [
                        'key' => 'report.overdue',
                        'label' => 'รายงานเกินกำหนด',
                        'icon' => 'clock-alert',
                        'abilities' => ['report.overdue'],
                        'route' => 'report.overdue',
                        'active' => 'report.overdue',
                    ],
                    [
                        'key' => 'report.damage',
                        'label' => 'รายงานความเสียหาย',
                        'icon' => 'triangle-alert',
                        'abilities' => ['report.damage'],
                        'route' => 'report.damage',
                        'active' => 'report.damage',
                    ],
                ],
            ],
            [
                'key' => 'administration',
                'label' => 'ผู้ดูแลระบบ',
                'items' => [
                    [
                        'key' => 'admin.users',
                        'label' => 'ผู้ใช้งาน',
                        'icon' => 'users',
                        'abilities' => ['user.manage'],
                        'route' => 'admin.users',
                        'active' => 'admin.users*',
                    ],
                    [
                        'key' => 'admin.roles',
                        'label' => 'บทบาท',
                        'icon' => 'shield-check',
                        'abilities' => ['role.manage'],
                        'route' => 'admin.roles',
                        'active' => 'admin.roles*',
                    ],
                    [
                        'key' => 'admin.permissions',
                        'label' => 'สิทธิ์การใช้งาน',
                        'icon' => 'key-round',
                        'abilities' => ['permission.manage'],
                        'route' => 'admin.permissions',
                        'active' => 'admin.permissions',
                    ],
                    [
                        'key' => 'admin.settings',
                        'label' => 'ตั้งค่าระบบ',
                        'icon' => 'settings',
                        'abilities' => ['settings.manage'],
                        'route' => 'admin.settings',
                        'active' => 'admin.settings*',
                    ],
                    [
                        'key' => 'audit.index',
                        'label' => 'บันทึกกิจกรรม',
                        'icon' => 'scroll-text',
                        'abilities' => ['audit.view'],
                        'route' => 'audit.index',
                        'active' => 'audit.*',
                    ],
                ],
            ],
        ];

        return array_values(array_filter(array_map(
            function (array $group) use ($user): array {
                $group['items'] = array_values(array_filter(
                    $group['items'],
                    fn (array $item): bool => $item['abilities'] === [] || $user->canAny($item['abilities']),
                ));

                return $group;
            },
            $groups,
        ), fn (array $group): bool => $group['items'] !== []));
    }
}
