<?php

namespace App\Support\Auditing;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class SecurityEventLogger
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $context */
    public function record(?User $user, string $event, array $context = []): void
    {
        Log::notice('security.event', [
            'event' => $event,
            'user_id' => $user?->getKey(),
            'route' => request()?->route()?->getName(),
            'ip_address' => request()?->ip(),
            ...$context,
        ]);

        try {
            $this->auditLogger->record($user, $event, $user, [], $context);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
