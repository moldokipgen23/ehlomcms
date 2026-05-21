<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Ensures an admin account exists, driven by env vars set in the Bunny
 * dashboard (ADMIN_EMAIL / ADMIN_PASSWORD). Idempotent — safe to run on
 * every deploy. No credentials are stored in the repo.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('AdminUserSeeder skipped — set ADMIN_EMAIL and ADMIN_PASSWORD env vars.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );

        $this->command?->info('Admin account ensured for '.$email);
    }
}
