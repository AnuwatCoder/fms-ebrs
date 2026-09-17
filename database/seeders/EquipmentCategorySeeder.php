<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use Illuminate\Database\Seeder;

class EquipmentCategorySeeder extends Seeder
{
    /** @var array<string, string> */
    private const CATEGORIES = [
        'COMPUTER' => 'Computer',
        'NOTEBOOK' => 'Notebook',
        'PROJECTOR' => 'Projector',
        'CAMERA' => 'Camera',
        'VIDEO_CAMERA' => 'Video Camera',
        'MICROPHONE' => 'Microphone',
        'AUDIO' => 'Audio Equipment',
        'PRESENTATION' => 'Presentation Equipment',
        'ACCESSORIES' => 'Accessories',
        'OTHER' => 'Other',
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
