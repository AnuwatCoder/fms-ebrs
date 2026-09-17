<?php

namespace App\Enums;

enum IncidentType: string
{
    case Maintenance = 'MAINTENANCE';
    case Damaged = 'DAMAGED';
    case Lost = 'LOST';
    case Other = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::Maintenance => 'บำรุงรักษา',
            self::Damaged => 'ชำรุด',
            self::Lost => 'สูญหาย',
            self::Other => 'อื่น ๆ',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Maintenance => 'badge-soft-warning',
            self::Damaged, self::Lost => 'badge-soft-danger',
            self::Other => 'badge-soft-warning',
        };
    }

    public function equipmentStatus(): EquipmentStatus
    {
        return match ($this) {
            self::Maintenance, self::Other => EquipmentStatus::Maintenance,
            self::Damaged => EquipmentStatus::Damaged,
            self::Lost => EquipmentStatus::Lost,
        };
    }
}
