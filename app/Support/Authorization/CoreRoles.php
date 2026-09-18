<?php

namespace App\Support\Authorization;

final class CoreRoles
{
    /** @var list<string> */
    public const NAMES = ['SuperAdmin', 'Admin', 'Approver', 'Staff', 'Borrower'];

    /** @var array<string, string> */
    private const LABELS = [
        'SuperAdmin' => 'ผู้ดูแลระบบสูงสุด',
        'Admin' => 'ผู้ดูแลระบบ',
        'Approver' => 'ผู้อนุมัติ',
        'Staff' => 'เจ้าหน้าที่',
        'Borrower' => 'ผู้ยืม',
    ];

    public static function contains(string $roleName): bool
    {
        return in_array($roleName, self::NAMES, true);
    }

    public static function label(string $roleName): string
    {
        return self::LABELS[$roleName] ?? $roleName;
    }
}
