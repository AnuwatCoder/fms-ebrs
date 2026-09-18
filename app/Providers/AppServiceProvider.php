<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\RolePolicy;
use App\Services\Authentication\AuthentikAccessService;
use App\View\Composers\HeaderComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Authentik\Provider as AuthentikProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            AuthentikAccessService::class,
            fn (): AuthentikAccessService => new AuthentikAccessService(
                allowedFacultyIds: config('authentik.access.allowed_faculty_ids', []),
                allowedAccountTypes: config('authentik.access.allowed_account_types', []),
            ),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        View::composer('layouts.partials.header', HeaderComposer::class);

        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('authentik', AuthentikProvider::class);
        });

        Gate::before(function (User $user): ?bool {
            return $user->hasRole('SuperAdmin') ? true : null;
        });
        Gate::policy(Role::class, RolePolicy::class);

        RateLimiter::for('authentik', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('borrow-submissions', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->user()?->getAuthIdentifier() ?? $request->ip());
        });

        RateLimiter::for('workflow', function (Request $request): Limit {
            return Limit::perMinute(30)->by($request->user()?->getAuthIdentifier() ?? $request->ip());
        });
    }
}
