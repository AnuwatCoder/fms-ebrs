<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\ApplyRoleSimulation;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', AddSecurityHeaders::class);
        $middleware->appendToGroup('web', ApplyRoleSimulation::class);
        $middleware->appendToGroup('web', EnsureUserIsActive::class);

        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (AuthorizationException $exception): void {
            Log::warning('security.authorization_denied', [
                'user_id' => request()->user()?->getAuthIdentifier(),
                'route' => request()->route()?->getName(),
                'ip_address' => request()->ip(),
                'exception' => $exception::class,
            ]);
        });
    })->create();
