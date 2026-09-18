<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class BorrowRequestInternalNotification extends Notification
{
    /**
     * @param  array<string, int|string>  $routeParameters
     */
    public function __construct(
        public string $event,
        public int $borrowRequestId,
        public string $requestNo,
        public string $title,
        public string $message,
        public string $icon = 'bell',
        public string $tone = 'primary',
        public string $route = 'borrow.show',
        public array $routeParameters = [],
    ) {
        $this->routeParameters = $routeParameters ?: ['borrowRequest' => $borrowRequestId];
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'borrow_request_id' => $this->borrowRequestId,
            'request_no' => $this->requestNo,
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'tone' => $this->tone,
            'route' => $this->route,
            'route_parameters' => $this->routeParameters,
        ];
    }
}
