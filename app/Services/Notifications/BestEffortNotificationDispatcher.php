<?php

namespace App\Services\Notifications;

use Closure;
use Throwable;

class BestEffortNotificationDispatcher
{
    /** @param Closure(): mixed $callback */
    public function dispatch(Closure $callback): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
