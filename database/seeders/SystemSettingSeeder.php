<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SystemSetting::definitions() as $key => $definition) {
            SystemSetting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'value' => $this->serialize($definition['default'], $definition['type']),
                    'type' => $definition['type'],
                ],
            );
        }
    }

    private function serialize(string|int|bool $value, string $type): string
    {
        return $type === 'boolean'
            ? ($value ? '1' : '0')
            : (string) $value;
    }
}
