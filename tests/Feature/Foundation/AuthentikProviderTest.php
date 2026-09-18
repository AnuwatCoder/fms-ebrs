<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

it('redirects authentication requests through the Authentik provider', function () {
    expect(route('auth.authentik.callback', absolute: false))
        ->toBe('/auth/authentik/callback');

    config()->set('services.authentik', [
        'base_url' => 'https://auth.example.test',
        'client_id' => 'ebrs-client',
        'client_secret' => 'test-secret',
        'redirect' => route('auth.authentik.callback'),
    ]);

    $response = $this->get(route('auth.authentik.redirect'));

    $response->assertRedirect();

    $location = (string) $response->headers->get('Location');

    expect($location)
        ->toStartWith('https://auth.example.test/application/o/authorize/?')
        ->toContain('client_id=ebrs-client')
        ->toContain('scope=openid+goauthentik.io%2Fapi+profile+email+psu_profile');
});

it('rejects Authentik redirects when its required configuration is missing', function () {
    config()->set('services.authentik', [
        'base_url' => null,
        'client_id' => null,
        'client_secret' => null,
        'redirect' => null,
    ]);

    $this->get(route('auth.authentik.redirect'))
        ->assertServiceUnavailable();
});

it('adds configured scopes to the Authentik authorization request', function () {
    config()->set('services.authentik', [
        'base_url' => 'https://auth.example.test',
        'client_id' => 'ebrs-client',
        'client_secret' => 'test-secret',
        'redirect' => route('auth.authentik.callback'),
        'scopes' => ['groups', 'offline_access'],
    ]);

    $response = $this->get(route('auth.authentik.redirect'));

    $response->assertRedirect();

    $location = (string) $response->headers->get('Location');
    parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
    $scopes = explode(' ', (string) ($query['scope'] ?? ''));

    expect($scopes)
        ->toContain('openid')
        ->toContain('goauthentik.io/api')
        ->toContain('profile')
        ->toContain('email')
        ->toContain('psu_profile')
        ->toContain('groups')
        ->toContain('offline_access');
});

it('denies Authentik users whose access claims are not allowed', function () {
    configureAuthentikForCallback();
    mockAuthentikUser([
        'sub' => 'denied-subject',
        'name' => 'Denied User',
        'email' => 'denied@example.com',
        'faculty_id' => 12,
        'account_type' => 'Staff',
    ]);

    $this->get(route('auth.authentik.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHas(
            'error',
            'บัญชีนี้ไม่มีสิทธิ์เข้าใช้งานระบบ กรุณาใช้บัญชีบุคลากรหรืออาจารย์ของคณะที่กำหนด',
        );

    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
});

it('authenticates Authentik users with allowed access claims', function () {
    configureAuthentikForCallback();
    mockAuthentikUser([
        'sub' => 'allowed-subject',
        'name' => 'Allowed Professor',
        'email' => 'professor@example.com',
        'preferred_username' => 'professor',
        'faculty_id' => '11',
        'account_type' => 'Professor',
    ]);

    $this->get(route('auth.authentik.callback'))
        ->assertRedirect(route('dashboard'));

    $user = User::query()->sole();

    $this->assertAuthenticatedAs($user);
    expect($user->identities()->where([
        'provider' => 'authentik',
        'subject' => 'allowed-subject',
    ])->exists())->toBeTrue();
});

function configureAuthentikForCallback(): void
{
    config()->set('services.authentik', [
        'base_url' => 'https://auth.example.test',
        'client_id' => 'ebrs-client',
        'client_secret' => 'test-secret',
        'redirect' => route('auth.authentik.callback'),
    ]);
}

/** @param array<string, mixed> $claims */
function mockAuthentikUser(array $claims): void
{
    $user = (new SocialiteUser)->setRaw($claims)->map([
        'id' => $claims['sub'],
        'name' => $claims['name'] ?? null,
        'email' => $claims['email'] ?? null,
    ]);
    $provider = Mockery::mock();
    $provider->shouldReceive('user')->once()->andReturn($user);

    Socialite::shouldReceive('driver')
        ->once()
        ->with('authentik')
        ->andReturn($provider);
}
