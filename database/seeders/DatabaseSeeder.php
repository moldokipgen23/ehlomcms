<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')],
        );

        Tenant::updateOrCreate(
            ['subdomain' => 'testshop1'],
            ['name' => 'E-Shop Electronics', 'site_type' => 'shopping', 'status' => 'active'],
        );

        Tenant::updateOrCreate(
            ['subdomain' => 'testshop2'],
            ['name' => 'Fashion Hub Store', 'site_type' => 'shopping', 'status' => 'active'],
        );

        Tenant::updateOrCreate(
            ['subdomain' => 'testinfo1'],
            ['name' => 'Green Valley Info', 'site_type' => 'info', 'status' => 'active'],
        );
    }
}
