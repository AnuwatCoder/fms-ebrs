<?php

namespace App\Enums;

enum BorrowRequestStatus: string
{
    case Draft = 'DRAFT';
    case Pending = 'PENDING';
    case Approved = 'APPROVED';
    case ReadyForPickup = 'READY_FOR_PICKUP';
    case Borrowed = 'BORROWED';
    case Returned = 'RETURNED';
    case Rejected = 'REJECTED';
    case Cancelled = 'CANCELLED';
    case Overdue = 'OVERDUE';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Pending, self::Cancelled],
            self::Pending => [self::Approved, self::Rejected, self::Cancelled],
            self::Approved => [self::ReadyForPickup, self::Cancelled],
            self::ReadyForPickup => [self::Borrowed, self::Cancelled],
            self::Borrowed => [self::Returned, self::Overdue],
            self::Overdue => [self::Returned],
            self::Returned, self::Rejected, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'ฉบับร่าง',
            self::Pending => 'รออนุมัติ',
            self::Approved => 'อนุมัติแล้ว',
            self::ReadyForPickup => 'พร้อมรับอุปกรณ์',
            self::Borrowed => 'กำลังยืม',
            self::Returned => 'คืนแล้ว',
            self::Rejected => 'ไม่อนุมัติ',
            self::Cancelled => 'ยกเลิก',
            self::Overdue => 'เกินกำหนด',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft, self::Cancelled => 'badge-soft-secondary',
            self::Pending => 'badge-soft-warning',
            self::Approved, self::Returned => 'badge-soft-success',
            self::ReadyForPickup => 'badge-soft-info',
            self::Borrowed => 'badge-soft-primary',
            self::Rejected, self::Overdue => 'badge-soft-danger',
        };
    }
}
