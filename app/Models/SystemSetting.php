<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    private const CACHE_PREFIX = 'system-setting:';

    protected $fillable = [
        'key',
        'value',
        'type',
        'updated_by',
    ];

    /**
     * @return array<string, array{
     *     label: string,
     *     type: string,
     *     default: string|int|bool,
     *     help: string,
     *     min?: int,
     *     max?: int
     * }>
     */
    public static function definitions(): array
    {
        return [
            'organization_name' => [
                'label' => 'ชื่อหน่วยงาน',
                'type' => 'string',
                'default' => 'Equipment Borrowing & Return System',
                'help' => 'ชื่อที่ใช้แสดงในหน้าระบบและรายงาน',
            ],
            'default_loan_days' => [
                'label' => 'จำนวนวันยืมเริ่มต้น',
                'type' => 'integer',
                'default' => 7,
                'min' => 1,
                'max' => 365,
                'help' => 'ใช้กำหนดวันที่คืนเริ่มต้นในแบบฟอร์มคำขอ',
            ],
            'max_items_per_request' => [
                'label' => 'จำนวนอุปกรณ์สูงสุดต่อคำขอ',
                'type' => 'integer',
                'default' => 10,
                'min' => 1,
                'max' => 100,
                'help' => 'จำกัดจำนวนรายการอุปกรณ์ในคำขอเดียว',
            ],
            'overdue_alert_days' => [
                'label' => 'แจ้งเตือนก่อนถึงกำหนดคืน',
                'type' => 'integer',
                'default' => 2,
                'min' => 0,
                'max' => 30,
                'help' => 'จำนวนวันล่วงหน้าสำหรับใช้สร้างการแจ้งเตือน',
            ],
            'contact_email' => [
                'label' => 'อีเมลติดต่อผู้ดูแล',
                'type' => 'email',
                'default' => '',
                'help' => 'แสดงเป็นช่องทางติดต่อเมื่อผู้ใช้ต้องการความช่วยเหลือ',
            ],
            'email_notifications_enabled' => [
                'label' => 'เปิดการส่งอีเมลแจ้งเตือน',
                'type' => 'boolean',
                'default' => true,
                'help' => 'ส่งอีเมลเมื่อมีคำขอยืมใหม่และเมื่อคำขอได้รับการอนุมัติ',
            ],
            'allow_weekend_borrow' => [
                'label' => 'อนุญาตให้เลือกวันยืมในวันหยุดสุดสัปดาห์',
                'type' => 'boolean',
                'default' => true,
                'help' => 'ใช้เป็นนโยบายกลางสำหรับการสร้างคำขอ',
            ],
        ];
    }

    public static function read(string $key): string|int|bool|null
    {
        $definition = self::definitions()[$key] ?? null;

        if ($definition === null) {
            return null;
        }

        $value = Cache::remember(
            self::CACHE_PREFIX.$key,
            now()->addHour(),
            fn (): string => (string) (self::query()->where('key', $key)->value('value') ?? $definition['default']),
        );

        return match ($definition['type']) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }

    public static function forgetCached(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX.$key);
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted(): void
    {
        static::saved(fn (SystemSetting $setting) => self::forgetCached($setting->key));
        static::deleted(fn (SystemSetting $setting) => self::forgetCached($setting->key));
    }
}
