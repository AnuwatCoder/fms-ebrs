<?php

namespace App\Actions\Administration;

use App\Models\SystemSetting;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateSystemSettings
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $values */
    public function execute(User $actor, array $values): void
    {
        $definitions = SystemSetting::definitions();
        $oldValues = collect(array_keys($definitions))
            ->mapWithKeys(fn (string $key): array => [$key => SystemSetting::read($key)])
            ->all();

        DB::transaction(function () use ($actor, $values, $definitions, $oldValues): void {
            foreach ($definitions as $key => $definition) {
                $value = $values[$key];

                if ($definition['type'] === 'boolean') {
                    $value = $value ? '1' : '0';
                }

                SystemSetting::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => (string) $value,
                        'type' => $definition['type'],
                        'updated_by' => $actor->id,
                    ],
                );
            }

            $this->auditLogger->record($actor, 'settings.updated', null, $oldValues, $values);
        });

        foreach (array_keys($definitions) as $key) {
            SystemSetting::forgetCached($key);
        }
    }
}
