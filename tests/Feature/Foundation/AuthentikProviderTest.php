<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

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
        ->toContain('scope=openid+goauthentik.io%2Fapi+profile+email');
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
