<?php

namespace App\Services\Equipment;

use Illuminate\Support\Facades\DB;

class EquipmentCodeGenerator
{
    private const CATEGORY_SEQUENCE = 'equipment_category';

    private const EQUIPMENT_SEQUENCE = 'equipment';

    public function nextCategoryCode(): string
    {
        return $this->next(
            self::CATEGORY_SEQUENCE,
            'CAT',
            4,
            'equipment_categories',
            'code',
        );
    }

    public function nextEquipmentCode(): string
    {
        return $this->next(
            self::EQUIPMENT_SEQUENCE,
            'EQ',
            6,
            'equipment',
            'equipment_code',
        );
    }

    private function next(
        string $sequenceKey,
        string $prefix,
        int $padding,
        string $table,
        string $column,
    ): string {
        return DB::transaction(function () use ($sequenceKey, $prefix, $padding, $table, $column): string {
            DB::table('code_sequences')->insertOrIgnore([
                'key' => $sequenceKey,
                'next_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            do {
                $nextNumber = (int) DB::table('code_sequences')
                    ->where('key', $sequenceKey)
                    ->lockForUpdate()
                    ->value('next_number');

                DB::table('code_sequences')
                    ->where('key', $sequenceKey)
                    ->update([
                        'next_number' => $nextNumber + 1,
                        'updated_at' => now(),
                    ]);

                $code = sprintf('%s-%0'.$padding.'d', $prefix, $nextNumber);
            } while (DB::table($table)->where($column, $code)->exists());

            return $code;
        });
    }
}
