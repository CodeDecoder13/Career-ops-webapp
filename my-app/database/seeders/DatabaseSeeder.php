<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the single dashboard user from env. No self-registration exists —
     * this seeder is the only way a user account gets created.
     */
    public function run(): void
    {
        $email = env('SITE_EMAIL');
        $password = env('SITE_PASSWORD');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            $this->command?->error('SITE_EMAIL and SITE_PASSWORD must be set in .env before seeding.');

            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'career-ops-tracker',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );
    }
}
