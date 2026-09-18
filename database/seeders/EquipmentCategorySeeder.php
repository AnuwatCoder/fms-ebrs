<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use Illuminate\Database\Seeder;

class EquipmentCategorySeeder extends Seeder
{
    /** @var array<string, string> */
    private const CATEGORIES = [
        'POWER_STRIP' => 'รางปลั๊กไฟ (ปลั๊กพ่วง)',
        'NOTEBOOK' => 'โน๊ตบุ๊ค',
        'OTHER' => 'อื่น ๆ',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $code => $name) {
            $category = EquipmentCategory::withTrashed()->firstOrNew(['code' => $code]);
            $category->fill(['name' => $name, 'active' => true]);
            $category->deleted_at = null;
            $category->save();
        }
    }
}
