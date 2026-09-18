<?php

namespace App\Enums;

enum BorrowItemStatus: string
{
    case Draft = 'DRAFT';
    case Pending = 'PENDING';
    case Reserved = 'RESERVED';
    case ReadyForPickup = 'READY_FOR_PICKUP';
    case Borrowed = 'BORROWED';
    case Returned = 'RETURNED';
    case Rejected = 'REJECTED';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'ฉบับร่าง',
            self::Pending => 'รออนุมัติ',
            self::Reserved => 'จองแล้ว',
            self::ReadyForPickup => 'พร้อมรับ',
            self::Borrowed => 'กำลังยืม',
            self::Returned => 'คืนแล้ว',
            self::Rejected => 'ไม่อนุมัติ',
            self::Cancelled => 'ยกเลิก',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft, self::Cancelled => 'badge-soft-secondary',
            self::Pending => 'badge-soft-warning',
            self::Reserved, self::Returned => 'badge-soft-success',
            self::ReadyForPickup => 'badge-soft-info',
            self::Borrowed => 'badge-soft-primary',
            self::Rejected => 'badge-soft-danger',
        };
    }
}
