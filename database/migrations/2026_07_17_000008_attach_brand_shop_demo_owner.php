<?php

use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tenant = Tenant::where('subdomain', 'brandshopdemo')->first();

        if (!$tenant) {
            return;
        }

        $client = Client::updateOrCreate(
            ['email' => 'lead-demo@ehlom.com'],
            [
                'name' => 'Luma & Co Demo Owner',
                'business_name' => 'Luma & Co',
                'phone' => '+91 98765 43210',
                'whatsapp' => '+91 98765 43210',
                'address' => 'Bandra West, Mumbai, India',
                'notes' => 'Internal lead demo client for the Brand Shop Pro ecommerce storefront.',
                'status' => 'active',
            ]
        );

        $tenant->update(['client_id' => $client->id]);

        $user = User::updateOrCreate(
            ['email' => 'owner@brandshopdemo.ehlom.com'],
            [
                'name' => 'Luma Demo Owner',
                'password' => Hash::make(Str::random(64)),
                'tenant_id' => $tenant->id,
            ]
        );

        if (Schema::hasTable('roles') && Schema::hasColumn('users', 'role_id')) {
            $roleId = \DB::table('roles')->where('name', 'tenant_owner')->value('id')
                ?: \DB::table('roles')->where('name', 'staff')->value('id');

            if ($roleId && !$user->role_id) {
                $user->update(['role_id' => $roleId]);
            }
        }
    }

    public function down(): void
    {
        User::where('email', 'owner@brandshopdemo.ehlom.com')->delete();

        $tenant = Tenant::where('subdomain', 'brandshopdemo')->first();
        $client = Client::where('email', 'lead-demo@ehlom.com')->first();

        if ($tenant && $client && (int) $tenant->client_id === (int) $client->id) {
            $tenant->update(['client_id' => null]);
        }

        $client?->delete();
    }
};
