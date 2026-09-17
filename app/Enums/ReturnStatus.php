<?php

namespace App\Enums;

enum ReturnStatus: string
{
    case Normal = 'NORMAL';
    case Damaged = 'DAMAGED';
    case Lost = 'LOST';
    case MaintenanceRequired = 'MAINTENANCE_REQUIRED';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'ปกติ',
            self::Damaged => 'ชำรุด',
            self::Lost => 'สูญหาย',
            self::MaintenanceRequired => 'ต้องซ่อมบำรุง',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Normal => 'badge-soft-success',
            self::Damaged, self::Lost => 'badge-soft-danger',
            self::MaintenanceRequired => 'badge-soft-warning',
        };
    }

    public function equipmentStatus(): EquipmentStatus
    {
        return match ($this) {
            self::Normal => EquipmentStatus::Available,
            self::Damaged => EquipmentStatus::Damaged,
            self::Lost => EquipmentStatus::Lost,
            self::MaintenanceRequired => EquipmentStatus::Maintenance,
        };
    }
}
