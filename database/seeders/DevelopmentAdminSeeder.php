<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DevelopmentAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('auth.super_admin.email');

        if (! is_string($email) || trim($email) === '') {
            return;
        }

        $email = Str::lower(trim($email));

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => config('auth.super_admin.name', 'Development Administrator'),
                'username' => config('auth.super_admin.username', 'dev.admin'),
                'active' => true,
                'password' => null,
            ],
        );

        $user->syncRoles(Role::findOrCreate('SuperAdmin', 'web'));
    }
}
