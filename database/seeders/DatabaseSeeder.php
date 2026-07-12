<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use App\Models\Tenant;
use App\Models\TenantProduct;
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
        // Agency admin user (no tenant_id)
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')],
        );

        // Seed tenants
        $shop1 = Tenant::updateOrCreate(
            ['subdomain' => 'testshop1'],
            ['name' => 'E-Shop Electronics', 'site_type' => 'shopping', 'action_type' => 'razorpay', 'template_id' => 'shop', 'status' => 'active'],
        );

        $shop2 = Tenant::updateOrCreate(
            ['subdomain' => 'testshop2'],
            ['name' => 'Fashion Hub Store', 'site_type' => 'shopping', 'action_type' => 'whatsapp', 'template_id' => 'shop', 'status' => 'active'],
        );

        $info1 = Tenant::updateOrCreate(
            ['subdomain' => 'testinfo1'],
            ['name' => 'Green Valley Info', 'site_type' => 'info', 'action_type' => 'whatsapp', 'template_id' => 'info', 'whatsapp_number' => '919876543210', 'contact_phone' => '+91 98765 43210', 'contact_email' => 'hello@greenvalley.info', 'contact_address' => '123 Green Road, Bangalore', 'contact_hours' => 'Mon–Fri, 9 AM – 6 PM', 'status' => 'active'],
        );

        // Tenant users (for testing login on each subdomain)
        User::firstOrCreate(
            ['email' => 'admin@testshop1.com'],
            ['name' => 'Shop1 Admin', 'password' => bcrypt('password'), 'tenant_id' => $shop1->id],
        );

        User::firstOrCreate(
            ['email' => 'admin@testshop2.com'],
            ['name' => 'Shop2 Admin', 'password' => bcrypt('password'), 'tenant_id' => $shop2->id],
        );

        User::firstOrCreate(
            ['email' => 'admin@testinfo1.com'],
            ['name' => 'Info1 Admin', 'password' => bcrypt('password'), 'tenant_id' => $info1->id],
        );

        // Sample products for shopping tenants
        TenantProduct::updateOrCreate(
            ['tenant_id' => $shop1->id, 'name' => 'Wireless Headphones'],
            ['price' => 2499.00, 'category' => 'electronics', 'description' => 'Premium noise-cancelling wireless headphones with 30-hour battery.'],
        );
        TenantProduct::updateOrCreate(
            ['tenant_id' => $shop1->id, 'name' => 'Bluetooth Speaker'],
            ['price' => 1299.00, 'category' => 'electronics', 'description' => 'Portable waterproof speaker with deep bass.'],
        );
        TenantProduct::updateOrCreate(
            ['tenant_id' => $shop2->id, 'name' => 'Cotton T-Shirt'],
            ['price' => 599.00, 'category' => 'clothing', 'description' => 'Premium 100% organic cotton t-shirt.'],
        );
        TenantProduct::updateOrCreate(
            ['tenant_id' => $shop2->id, 'name' => 'Denim Jacket'],
            ['price' => 1999.00, 'category' => 'clothing', 'description' => 'Classic blue denim jacket, slim fit.'],
        );

        // Sample payment setting for testshop1 (Razorpay test keys)
        PaymentSetting::updateOrCreate(
            ['tenant_id' => $shop1->id],
            ['provider' => 'razorpay', 'api_key' => 'rzp_test_dummykey', 'api_secret' => 'test_secret_dummy'],
        );
    }
}
