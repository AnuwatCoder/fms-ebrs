<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Auditing\SecurityEventLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function __construct(private SecurityEventLogger $securityEvents) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->active) {
            return $next($request);
        }

        $this->securityEvents->record($user, 'auth.session.revoked', [
            'reason' => 'inactive_account',
        ]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('error', 'บัญชีผู้ใช้นี้ถูกระงับการใช้งาน');
    }
}
