<?php

use App\Models\Client;
use App\Models\Tenant;
use App\Models\TenantProduct;
use App\Models\TenantProductCategory;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $client = Client::updateOrCreate(
            ['email' => 'demo@emberandgrain.ehlom.com'],
            [
                'name' => 'Ember & Grain Demo Owner',
                'business_name' => 'Ember & Grain',
                'phone' => '+91 98765 43210',
                'whatsapp' => '+91 98765 43210',
                'address' => 'Churachandpur, Manipur, India',
                'notes' => 'Internal restaurant website demo for lead presentations. Replace with real client data before handover.',
                'status' => 'active',
            ]
        );

        $tenant = Tenant::updateOrCreate(
            ['subdomain' => 'restaurantdemo'],
            [
                'client_id' => $client->id,
                'name' => 'Ember & Grain',
                'site_type' => 'restaurant',
                'site_mode' => 'managed',
                'template_id' => 'restaurant',
                'status' => 'active',
                'plan' => 'Demo',
                'contact_email' => 'hello@emberandgrain.demo',
                'contact_phone' => '+91 98765 43210',
                'whatsapp_number' => '919876543210',
                'contact_address' => 'Main Market Road, Churachandpur, Manipur',
                'contact_hours' => 'Tue-Sun, 11:00 AM - 10:00 PM',
                'action_type' => 'whatsapp',
                'about_text' => 'Ember & Grain is a warm neighbourhood kitchen demo built to show how a restaurant can publish a polished menu, accept WhatsApp orders, collect table reservations, share its story, and manage the website from one focused dashboard.',
                'modules' => ['content', 'catalog', 'product_categories', 'product_gallery', 'reservations', 'orders', 'payments', 'marketing_sections', 'seo_booster'],
                'theme_settings' => [
                    'accent_color' => '#c56a3b',
                    'show_menu' => true,
                    'show_reservations' => true,
                    'show_about' => true,
                    'show_gallery' => false,
                    'show_contact' => true,
                ],
            ]
        );

        User::updateOrCreate(
            ['email' => 'owner@restaurantdemo.ehlom.com'],
            [
                'name' => 'Ember Demo Owner',
                'password' => Hash::make(Str::random(64)),
                'tenant_id' => $tenant->id,
            ]
        );

        $this->writeDemoAssets($tenant->id);

        $categories = [];
        foreach ([
            ['slug' => 'small-plates', 'name' => 'Small Plates', 'sort_order' => 1],
            ['slug' => 'mains', 'name' => 'Mains', 'sort_order' => 2],
            ['slug' => 'drinks', 'name' => 'Drinks', 'sort_order' => 3],
        ] as $category) {
            $categories[$category['slug']] = TenantProductCategory::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $category['slug']],
                $category + ['description' => null, 'image' => null, 'is_active' => true]
            );
        }

        foreach ($this->menu() as $index => $item) {
            TenantProduct::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $item['slug']],
                [
                    'tenant_product_category_id' => $categories[$item['category']]->id,
                    'name' => $item['name'],
                    'type' => 'simple',
                    'price' => $item['price'],
                    'photo' => "tenants/{$tenant->id}/restaurant-demo/{$item['image']}.svg",
                    'cover_image' => "tenants/{$tenant->id}/restaurant-demo/{$item['image']}.svg",
                    'category' => $categories[$item['category']]->name,
                    'description' => $item['description'],
                    'sku' => strtoupper(Str::slug($item['slug'], '')),
                    'stock' => 100,
                    'is_featured' => $item['featured'],
                    'is_top_seller' => $item['featured'],
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }

    public function down(): void
    {
        $tenant = Tenant::where('subdomain', 'restaurantdemo')->first();
        if ($tenant) {
            $tenant->delete();
        }

        Client::where('email', 'demo@emberandgrain.ehlom.com')->delete();
        User::where('email', 'owner@restaurantdemo.ehlom.com')->delete();
    }

    private function writeDemoAssets(int $tenantId): void
    {
        $directory = storage_path("app/public/tenants/{$tenantId}/restaurant-demo");
        File::ensureDirectoryExists($directory);

        foreach (['hero' => 'A warm kitchen for slow meals', 'starter' => 'Charred corn & lime', 'main' => 'Ember bowl', 'drink' => 'Citrus spritz', 'dessert' => 'Cocoa cloud'] as $key => $label) {
            File::put("{$directory}/{$key}.svg", $this->assetSvg($label, $key));
        }
    }

    private function assetSvg(string $label, string $key): string
    {
        $palette = [
            'hero' => ['#3b1f1b', '#d18458'],
            'starter' => ['#7c3f22', '#f1b45b'],
            'main' => ['#193b35', '#d78a55'],
            'drink' => ['#244b54', '#e8b968'],
            'dessert' => ['#3b2231', '#d37b67'],
        ][$key] ?? ['#3b1f1b', '#d18458'];

        return '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900">'
            . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="' . $palette[0] . '"/><stop offset="1" stop-color="' . $palette[1] . '"/></linearGradient></defs>'
            . '<rect width="1200" height="900" fill="url(#g)"/><circle cx="930" cy="235" r="180" fill="#ffffff" fill-opacity=".12"/><circle cx="280" cy="710" r="260" fill="#000000" fill-opacity=".14"/>'
            . '<text x="72" y="720" fill="#fffaf2" font-family="Georgia,serif" font-size="54" font-weight="700">' . e($label) . '</text>'
            . '<text x="76" y="772" fill="#fffaf2" fill-opacity=".75" font-family="Arial,sans-serif" font-size="22">Ember &amp; Grain demo menu</text></svg>';
    }

    private function menu(): array
    {
        return [
            ['slug' => 'charred-corn-lime', 'name' => 'Charred Corn & Lime', 'category' => 'small-plates', 'price' => 280, 'image' => 'starter', 'featured' => true, 'description' => 'Fire-kissed corn, smoked butter, lime, and toasted herbs.'],
            ['slug' => 'ember-bowl', 'name' => 'Ember Grain Bowl', 'category' => 'mains', 'price' => 520, 'image' => 'main', 'featured' => true, 'description' => 'Roasted vegetables, warm grains, greens, and house dressing.'],
            ['slug' => 'citrus-spritz', 'name' => 'Citrus Garden Spritz', 'category' => 'drinks', 'price' => 220, 'image' => 'drink', 'featured' => false, 'description' => 'Fresh citrus, basil, and sparkling water served over ice.'],
            ['slug' => 'cocoa-cloud', 'name' => 'Cocoa Cloud', 'category' => 'small-plates', 'price' => 260, 'image' => 'dessert', 'featured' => false, 'description' => 'Dark cocoa mousse, sea salt, and a crisp sesame finish.'],
        ];
    }
};
