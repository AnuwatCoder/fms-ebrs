<?php

namespace App\Enums;

enum IncidentState: string
{
    case Open = 'OPEN';
    case Resolved = 'RESOLVED';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'รอดำเนินการ',
            self::Resolved => 'แก้ไขแล้ว',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'badge-soft-warning',
            self::Resolved => 'badge-soft-success',
        };
    }
}
