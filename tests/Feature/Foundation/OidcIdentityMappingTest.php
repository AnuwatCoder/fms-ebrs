<?php

use App\Actions\Authentication\SyncOidcUser;
use App\Models\User;
use Database\Seeders\DevelopmentAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a local user keyed by the oidc provider and subject', function () {
    $user = app(SyncOidcUser::class)->execute('oidc_default', 'subject-123', [
        'name' => 'Anuwat Example',
        'email' => 'anuwat@example.com',
        'preferred_username' => 'anuwat',
    ]);

    expect($user)
        ->name->toBe('Anuwat Example')
        ->email->toBe('anuwat@example.com')
        ->username->toBe('anuwat')
        ->active->toBeTrue()
        ->and($user->password)->toBeNull();

    $this->assertDatabaseHas('user_identities', [
        'user_id' => $user->id,
        'provider' => 'oidc_default',
        'subject' => 'subject-123',
    ]);
});

it('supports an oidc user without local credentials or an email claim', function () {
    $user = app(SyncOidcUser::class)->execute('oidc_default', 'subject-without-email', [
        'name' => 'Passwordless User',
        'preferred_username' => 'passwordless',
    ]);

    expect($user->email)->toBeNull()
        ->and($user->password)->toBeNull();
});

it('links the configured initial super administrator on first oidc login', function () {
    config([
        'auth.super_admin.email' => 'INITIAL.ADMIN@example.com',
        'auth.super_admin.name' => 'Initial Administrator',
        'auth.super_admin.username' => 'initial.admin',
    ]);

    $this->seed(DevelopmentAdminSeeder::class);

    $seededUser = User::query()->where('email', 'initial.admin@example.com')->firstOrFail();

    $oidcUser = app(SyncOidcUser::class)->execute('authentik', 'initial-admin-subject', [
        'name' => 'Administrator from Authentik',
        'email' => 'initial.admin@example.com',
        'preferred_username' => 'authentik.admin',
    ]);

    expect($oidcUser->is($seededUser))->toBeTrue()
        ->and($oidcUser->hasRole('SuperAdmin'))->toBeTrue()
        ->and($oidcUser->identities()->where([
            'provider' => 'authentik',
            'subject' => 'initial-admin-subject',
        ])->exists())->toBeTrue();
});

it('reuses the same local user when the oidc profile changes', function () {
    $action = app(SyncOidcUser::class);

    $first = $action->execute('oidc_default', 'subject-123', [
        'name' => 'Old Name',
        'email' => 'anuwat@example.com',
    ]);

    $second = $action->execute('oidc_default', 'subject-123', [
        'name' => 'New Name',
        'email' => 'anuwat@example.com',
    ]);

    expect($second->is($first))->toBeTrue()
        ->and($second->name)->toBe('New Name')
        ->and(User::query()->count())->toBe(1);
});

it('does not restore the bootstrap super administrator role after it is removed', function () {
    config(['auth.super_admin.email' => 'initial.admin@example.com']);

    $action = app(SyncOidcUser::class);
    $user = $action->execute('authentik', 'initial-admin-subject', [
        'name' => 'Initial Administrator',
        'email' => 'initial.admin@example.com',
    ]);

    expect($user->hasRole('SuperAdmin'))->toBeTrue();

    $user->removeRole('SuperAdmin');

    $syncedUser = $action->execute('authentik', 'initial-admin-subject', [
        'name' => 'Initial Administrator',
        'email' => 'initial.admin@example.com',
    ]);

    expect($syncedUser->hasRole('SuperAdmin'))->toBeFalse();
});

it('does not automatically link an identity by email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    expect(fn () => app(SyncOidcUser::class)->execute('oidc_default', 'new-subject', [
        'email' => 'existing@example.com',
    ]))->toThrow(InvalidArgumentException::class);
});
