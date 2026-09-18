<?php

namespace App\Actions\Authentication;

use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;

class SyncOidcUser
{
    /**
     * @param  array<string, mixed>  $claims
     */
    public function execute(string $provider, string $subject, array $claims): User
    {
        $provider = trim($provider);
        $subject = trim($subject);

        if ($provider === '' || $subject === '') {
            throw new InvalidArgumentException('OIDC provider and subject are required.');
        }

        try {
            return $this->sync($provider, $subject, $claims);
        } catch (QueryException $exception) {
            // A concurrent first login may have created the same identity.
            $identity = UserIdentity::query()
                ->where('provider', $provider)
                ->where('subject', $subject)
                ->first();

            if ($identity === null) {
                throw $exception;
            }

            return $this->sync($provider, $subject, $claims);
        }
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private function sync(string $provider, string $subject, array $claims): User
    {
        return DB::transaction(function () use ($provider, $subject, $claims): User {
            $identity = UserIdentity::query()
                ->where('provider', $provider)
                ->where('subject', $subject)
                ->lockForUpdate()
                ->first();

            if ($identity !== null) {
                $user = $identity->user()->lockForUpdate()->firstOrFail();
                $this->refreshProfile($user, $claims, $subject);

                return $user;
            }

            $email = $this->emailClaim($claims);
            $user = $email === null
                ? null
                : User::query()->where('email', $email)->lockForUpdate()->first();

            if ($user !== null) {
                if (! $this->isConfiguredSuperAdminEmail($email)) {
                    throw new InvalidArgumentException(
                        'An account with this email already exists and must be linked by an administrator.'
                    );
                }

                $this->refreshProfile($user, $claims, $subject);
            } else {
                $user = User::query()->create([
                    'name' => $this->displayName($claims, $subject),
                    'email' => $email,
                    'username' => $this->availableUsername($claims, $subject),
                    'active' => true,
                    'password' => null,
                ]);
            }

            $user->identities()->create([
                'provider' => $provider,
                'subject' => $subject,
            ]);

            $this->grantConfiguredSuperAdminRole($user);

            return $user;
        }, attempts: 3);
    }

    /** @param array<string, mixed> $claims */
    private function refreshProfile(User $user, array $claims, string $subject): void
    {
        $name = $this->displayName($claims, $subject);
        $email = $this->emailClaim($claims);

        $user->name = $name;

        if ($email !== null && ($user->email === null || $user->email === $email)) {
            $user->email = $email;
        }

        if ($user->username === null) {
            $user->username = $this->availableUsername($claims, $subject, $user->id);
        }

        if ($user->isDirty()) {
            $user->save();
        }
    }

    private function grantConfiguredSuperAdminRole(User $user): void
    {
        if (! $this->isConfiguredSuperAdminEmail($user->email)) {
            return;
        }

        $user->assignRole(Role::findOrCreate('SuperAdmin', 'web'));
    }

    private function isConfiguredSuperAdminEmail(?string $email): bool
    {
        $configuredEmail = config('auth.super_admin.email');

        return $email !== null
            && is_string($configuredEmail)
            && trim($configuredEmail) !== ''
            && Str::lower(trim($email)) === Str::lower(trim($configuredEmail));
    }

    /** @param array<string, mixed> $claims */
    private function displayName(array $claims, string $subject): string
    {
        $name = $this->claim($claims, 'name');

        if ($name !== null) {
            return $name;
        }

        $parts = array_filter([
            $this->claim($claims, 'given_name'),
            $this->claim($claims, 'family_name'),
        ]);

        return $parts === [] ? $subject : implode(' ', $parts);
    }

    /** @param array<string, mixed> $claims */
    private function availableUsername(array $claims, string $subject, ?int $ignoreUserId = null): string
    {
        $preferred = $this->claim($claims, 'preferred_username')
            ?? Str::before((string) $this->claim($claims, 'email'), '@')
            ?: 'user';

        $base = Str::limit(Str::slug($preferred, '_'), 200, '');
        $base = $base === '' ? 'user' : $base;
        $username = $base;

        $exists = fn (string $candidate): bool => User::query()
            ->where('username', $candidate)
            ->when($ignoreUserId !== null, fn ($query) => $query->whereKeyNot($ignoreUserId))
            ->exists();

        if ($exists($username)) {
            $username = Str::limit($base, 180, '').'_'.substr(hash('sha256', $subject), 0, 12);
        }

        return $username;
    }

    /** @param array<string, mixed> $claims */
    private function claim(array $claims, string $key): ?string
    {
        $value = Arr::get($claims, $key);

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    /** @param array<string, mixed> $claims */
    private function emailClaim(array $claims): ?string
    {
        $email = $this->claim($claims, 'email');

        return $email === null ? null : Str::lower($email);
    }
}
