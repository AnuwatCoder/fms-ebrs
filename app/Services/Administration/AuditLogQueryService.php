<?php

namespace App\Services\Administration;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AuditLogQueryService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function index(array $filters): array
    {
        $event = trim((string) ($filters['event'] ?? ''));
        $causerId = (int) ($filters['causer'] ?? 0);
        $from = isset($filters['from']) ? Carbon::parse($filters['from']) : null;
        $to = isset($filters['to']) ? Carbon::parse($filters['to']) : null;

        return [
            'logs' => AuditLog::query()
                ->with('causer:id,name,email')
                ->when($event !== '', fn (Builder $query): Builder => $query->where('event', $event))
                ->when($causerId > 0, fn (Builder $query): Builder => $query->where('causer_id', $causerId))
                ->when($from, fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $from))
                ->when($to, fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $to))
                ->latest('created_at')
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
            'users' => User::query()->whereHas('roles')->orderBy('name')->get(['id', 'name']),
        ];
    }
}
