<?php

namespace App\Services\Notifications;

use App\Models\User;

class UserNotificationQueryService
{
    /** @return array<string, mixed> */
    public function header(User $user): array
    {
        return [
            'headerNotifications' => $user->notifications()->latest()->limit(6)->get(),
            'unreadNotificationCount' => $user->unreadNotifications()->count(),
        ];
    }
}
