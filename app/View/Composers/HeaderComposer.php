<?php

namespace App\View\Composers;

use App\Models\User;
use App\Services\Notifications\UserNotificationQueryService;
use App\Support\Navigation\Breadcrumbs;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeaderComposer
{
    public function __construct(
        private Guard $auth,
        private UserNotificationQueryService $notifications,
        private Breadcrumbs $breadcrumbs,
        private Request $request,
    ) {}

    public function compose(View $view): void
    {
        $user = $this->auth->user();

        if (! $user instanceof User) {
            $view->with([
                'headerBreadcrumbs' => [],
                'headerNotifications' => collect(),
                'unreadNotificationCount' => 0,
            ]);

            return;
        }

        $view->with([
            'headerBreadcrumbs' => $this->breadcrumbs->parents($this->request, $user),
            ...$this->notifications->header($user),
        ]);
    }
}
