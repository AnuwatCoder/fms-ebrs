<?php

namespace App\Support\Authorization;

final class CoreRoles
{
    /** @var list<string> */
    public const NAMES = ['SuperAdmin', 'Admin', 'Approver', 'Staff', 'Borrower'];

    public static function contains(string $roleName): bool
    {
        return in_array($roleName, self::NAMES, true);
    }
}
