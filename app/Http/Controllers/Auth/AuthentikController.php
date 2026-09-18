<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Authentication\SyncOidcUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Authentication\AuthentikAccessService;
use App\Support\Auditing\SecurityEventLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthentikController extends Controller
{
    private const REQUIRED_SCOPES = ['psu_profile'];

    public function redirect(): RedirectResponse
    {
        $this->ensureConfigured();

        return Socialite::driver('authentik')
            ->scopes($this->configuredScopes())
            ->redirect();
    }

    public function callback(
        Request $request,
        SyncOidcUser $syncOidcUser,
        AuthentikAccessService $authentikAccess,
        SecurityEventLogger $securityEvents,
    ): RedirectResponse {
        $this->ensureConfigured();

        try {
            $authentikUser = Socialite::driver('authentik')->user();
            $claims = $authentikUser->getRaw();
            $claims['email'] ??= $authentikUser->getEmail();
            $claims['name'] ??= $authentikUser->getName();

            if (! $authentikAccess->allows($claims)) {
                $securityEvents->record(null, 'auth.login.denied', [
                    'provider' => 'authentik',
                    'reason' => 'claims_not_allowed',
                ]);

                return to_route('login')->with(
                    'error',
                    'บัญชีนี้ไม่มีสิทธิ์เข้าใช้งานระบบ กรุณาใช้บัญชีบุคลากรหรืออาจารย์ของคณะที่กำหนด',
                );
            }

            $user = $syncOidcUser->execute(
                provider: 'authentik',
                subject: (string) $authentikUser->getId(),
                claims: $claims,
            );

            if (! $user->active) {
                $securityEvents->record($user, 'auth.login.denied', [
                    'provider' => 'authentik',
                    'reason' => 'inactive_account',
                ]);

                return to_route('login')->with('error', 'บัญชีผู้ใช้นี้ถูกระงับการใช้งาน');
            }

            Auth::login($user);
            $request->session()->regenerate();
            $securityEvents->record($user, 'auth.login.succeeded', [
                'provider' => 'authentik',
            ]);

            return redirect()->intended(route('dashboard'));
        } catch (Throwable $exception) {
            $securityEvents->record(null, 'auth.login.failed', [
                'provider' => 'authentik',
                'exception' => $exception::class,
            ]);
            report($exception);

            return to_route('login')->with('error', 'ไม่สามารถเข้าสู่ระบบผ่าน Authentik ได้ กรุณาลองใหม่หรือติดต่อผู้ดูแลระบบ');
        }
    }

    public function logout(Request $request, SecurityEventLogger $securityEvents): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User) {
            $securityEvents->record($user, 'auth.logout');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }

    private function ensureConfigured(): void
    {
        $connection = config('services.authentik');

        $baseUrl = is_array($connection) ? ($connection['base_url'] ?? null) : null;
        $redirect = is_array($connection) ? ($connection['redirect'] ?? null) : null;
        $requiresHttps = app()->environment('production');

        abort_unless(
            is_array($connection)
                && filled($baseUrl)
                && filled($connection['client_id'] ?? null)
                && filled($connection['client_secret'] ?? null)
                && filled($redirect)
                && (! $requiresHttps || str_starts_with((string) $baseUrl, 'https://'))
                && (! $requiresHttps || str_starts_with((string) $redirect, 'https://')),
            503,
            'Authentik is not configured.'
        );
    }

    /** @return list<string> */
    private function configuredScopes(): array
    {
        $scopes = config('services.authentik.scopes', []);

        if (! is_array($scopes)) {
            return self::REQUIRED_SCOPES;
        }

        return array_values(array_unique([
            ...self::REQUIRED_SCOPES,
            ...array_filter(
                $scopes,
                fn (mixed $scope): bool => is_string($scope) && filled($scope),
            ),
        ]));
    }
}
