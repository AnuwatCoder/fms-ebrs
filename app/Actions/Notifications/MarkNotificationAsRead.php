<?php

namespace App\Actions\Notifications;

use App\Models\User;

class MarkNotificationAsRead
{
    /** @return array<string, mixed> */
    public function execute(User $user, string $notificationId): array
    {
        $notification = $user->notifications()->whereKey($notificationId)->firstOrFail();
        $notification->markAsRead();

        return $notification->data;
    }
}
