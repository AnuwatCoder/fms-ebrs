<?php

namespace App\Enums;

enum EquipmentStatus: string
{
    case Available = 'AVAILABLE';
    case Reserved = 'RESERVED';
    case Borrowed = 'BORROWED';
    case Maintenance = 'MAINTENANCE';
    case Damaged = 'DAMAGED';
    case Lost = 'LOST';
    case Inactive = 'INACTIVE';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'พร้อมให้ยืม',
            self::Reserved => 'จองแล้ว',
            self::Borrowed => 'กำลังถูกยืม',
            self::Maintenance => 'อยู่ระหว่างซ่อมบำรุง',
            self::Damaged => 'ชำรุด',
            self::Lost => 'สูญหาย',
            self::Inactive => 'ปิดใช้งาน',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Available => 'badge-soft-success',
            self::Reserved => 'badge-soft-info',
            self::Borrowed => 'badge-soft-primary',
            self::Maintenance => 'badge-soft-warning',
            self::Damaged, self::Lost => 'badge-soft-danger',
            self::Inactive => 'badge-soft-secondary',
        };
    }

    /** @return list<self> */
    public static function incidentResolutionOptions(): array
    {
        return [
            self::Available,
            self::Maintenance,
            self::Damaged,
            self::Lost,
            self::Inactive,
        ];
    }
}
