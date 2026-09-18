<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Authentication\SyncOidcUser;
use App\Http\Controllers\Controller;
use App\Services\Authentication\AuthentikAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthentikController extends Controller
{
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
    ): RedirectResponse {
        $this->ensureConfigured();

        try {
            $authentikUser = Socialite::driver('authentik')->user();
            $claims = $authentikUser->getRaw();
            $claims['email'] ??= $authentikUser->getEmail();
            $claims['name'] ??= $authentikUser->getName();

            if (! $authentikAccess->allows($claims)) {
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
                return to_route('login')->with('error', 'บัญชีผู้ใช้นี้ถูกระงับการใช้งาน');
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        } catch (Throwable $exception) {
            report($exception);

            return to_route('login')->with('error', 'ไม่สามารถเข้าสู่ระบบผ่าน Authentik ได้ กรุณาลองใหม่หรือติดต่อผู้ดูแลระบบ');
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }

    private function ensureConfigured(): void
    {
        $connection = config('services.authentik');

        abort_unless(
            is_array($connection)
                && filled($connection['base_url'] ?? null)
                && filled($connection['client_id'] ?? null)
                && filled($connection['client_secret'] ?? null)
                && filled($connection['redirect'] ?? null),
            503,
            'Authentik is not configured.'
        );
    }

    /** @return list<string> */
    private function configuredScopes(): array
    {
        $scopes = config('services.authentik.scopes', []);

        if (! is_array($scopes)) {
            return [];
        }

        return array_values(array_filter(
            $scopes,
            fn (mixed $scope): bool => is_string($scope) && filled($scope),
        ));
    }
}
