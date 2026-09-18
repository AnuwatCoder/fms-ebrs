<?php

namespace App\Http\Controllers\Notifications;

use App\Actions\Notifications\MarkAllNotificationsAsRead;
use App\Actions\Notifications\MarkNotificationAsRead;
use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationActionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

class NotificationController extends Controller
{
    public function read(
        NotificationActionRequest $request,
        string $notification,
        MarkNotificationAsRead $markAsRead,
    ): RedirectResponse {
        $data = $markAsRead->execute($request->user(), $notification);
        $route = $data['route'] ?? null;

        if (is_string($route) && Route::has($route)) {
            $parameters = is_array($data['route_parameters'] ?? null)
                ? $data['route_parameters']
                : [];

            return to_route($route, $parameters);
        }

        return to_route('dashboard');
    }

    public function readAll(
        NotificationActionRequest $request,
        MarkAllNotificationsAsRead $markAllAsRead,
    ): RedirectResponse {
        $markAllAsRead->execute($request->user());

        return back();
    }
}
