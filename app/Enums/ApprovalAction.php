<?php

namespace App\Enums;

enum ApprovalAction: string
{
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'อนุมัติ',
            self::Rejected => 'ไม่อนุมัติ',
        };
    }
}
