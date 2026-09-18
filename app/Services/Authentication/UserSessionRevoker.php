<?php

namespace App\Services\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserSessionRevoker
{
    public function revoke(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        try {
            DB::connection(config('session.connection'))
                ->table((string) config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->delete();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
