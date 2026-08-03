<?php

use App\Models\Tenant;
use App\Models\TenantMarketingSection;
use App\Models\TenantProduct;
use App\Models\TenantProductCategory;
use App\Models\TenantProductCollection;
use App\Models\TenantProductImage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tenant = Tenant::where('subdomain', 'brandshopdemo')->first();

        if (!$tenant) {
            return;
        }

        $this->clearDemoCatalog($tenant);

        $assets = $this->prepareAssets($tenant->id);

        $tenant->update([
            'name' => 'ModeHaus',
            'banner_image' => $assets['hero'] ?? null,
            'logo' => null,
            'about_text' => 'ModeHaus is a modern fashion and accessories demo store built for lead presentations. It shows how a real shopping client can launch categories, product pages, cart, checkout, policies, customer accounts, and WhatsApp orders from one ecommerce dashboard.',
            'contact_email' => 'hello@modehaus.demo',
            'contact_phone' => '+91 98765 43210',
            'contact_address' => 'Fashion District, Mumbai, India',
            'contact_hours' => 'Mon-Sat, 10:00 AM - 8:00 PM',
            'theme_settings' => array_merge($tenant->theme_settings ?? [], [
                'accent_color' => '#b45309',
                'store_hero_eyebrow' => 'Fashion Demo Store',
                'store_hero_title' => 'Modern fashion, styled for everyday confidence',
                'store_hero_subtitle' => 'A complete lead-ready ecommerce demo for apparel and accessories: men, women, wallets, sunglasses, bags, product detail pages, cart, and WhatsApp checkout.',
                'store_primary_cta' => 'Shop Fashion',
                'store_secondary_cta' => 'Browse Collections',
                'featured_products_title' => 'New Season Picks',
                'collections_title' => 'Shop by Style',
                'about_title' => 'A Real Store Experience',
                'store_highlight_1' => 'Men and women apparel',
                'store_highlight_2' => 'Accessories catalog',
                'store_highlight_3' => 'Checkout-ready products',
                'shipping_promise' => 'Fast dispatch workflow',
                'quality_promise' => 'Premium product presentation',
                'support_promise' => 'WhatsApp order support',
                'return_policy' => 'Clear exchange policy',
                'footer_tagline' => 'Fashion and accessories ecommerce demo powered by Ehlom OS.',
                'footer_about' => 'ModeHaus is demo data for lead presentations only. Replace the products, images, categories, and copy with each client brand.',
            ]),
        ]);

        if ($tenant->client) {
            $tenant->client->update([
                'name' => 'ModeHaus Demo Owner',
                'business_name' => 'ModeHaus',
                'notes' => 'Internal lead demo client for the Brand Shop Pro fashion and accessories storefront.',
            ]);
        }

        $categories = [];
        foreach ($this->categories() as $index => $data) {
            $categories[$data['slug']] = TenantProductCategory::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'image' => $assets[$data['image']] ?? null,
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }

        $collections = [];
        foreach ($this->collections() as $index => $data) {
            $collections[$data['slug']] = TenantProductCollection::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'cover_image' => $assets[$data['image']] ?? null,
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }

        foreach ($this->products() as $index => $data) {
            $product = TenantProduct::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $data['slug']],
                [
                    'tenant_product_category_id' => $categories[$data['category']]?->id,
                    'name' => $data['name'],
                    'type' => 'simple',
                    'price' => $data['price'],
                    'photo' => $assets[$data['image']] ?? null,
                    'cover_image' => $assets[$data['image']] ?? null,
                    'category' => $categories[$data['category']]?->name,
                    'description' => $data['description'],
                    'sku' => strtoupper(Str::slug($data['slug'], '')),
                    'stock' => $data['stock'],
                    'material' => $data['material'],
                    'care_instructions' => $data['care'],
                    'heritage_note' => 'Demo fashion product for lead presentation.',
                    'is_top_seller' => $data['featured'],
                    'is_featured' => $data['featured'],
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );

            $product->collections()->sync(
                collect($data['collections'])
                    ->map(fn ($slug) => $collections[$slug]?->id)
                    ->filter()
                    ->values()
                    ->all()
            );

            foreach (array_values(array_unique(array_merge([$data['image']], $data['gallery']))) as $galleryIndex => $assetKey) {
                if (empty($assets[$assetKey])) {
                    continue;
                }

                TenantProductImage::updateOrCreate(
                    ['tenant_product_id' => $product->id, 'image_path' => $assets[$assetKey]],
                    ['tenant_product_color_id' => null, 'sort_order' => $galleryIndex]
                );
            }
        }

        foreach ($this->marketingSections() as $index => $section) {
            TenantMarketingSection::updateOrCreate(
                ['tenant_id' => $tenant->id, 'title' => $section['title']],
                [
                    'type' => $section['type'],
                    'display_style' => $section['display_style'],
                    'items_per_row' => $section['items_per_row'],
                    'filter_type' => $section['filter_type'],
                    'filter_value' => $section['filter_value'],
                    'sort_order' => $index,
                    'is_enabled' => true,
                ]
            );
        }
    }

    public function down(): void
    {
        // Demo seed only. Leave the tenant in place if rolled back manually.
    }

    private function clearDemoCatalog(Tenant $tenant): void
    {
        $productIds = TenantProduct::where('tenant_id', $tenant->id)->pluck('id');

        if ($productIds->isNotEmpty()) {
            TenantProductImage::whereIn('tenant_product_id', $productIds)->delete();
            DB::table('tenant_product_collection_product')->whereIn('tenant_product_id', $productIds)->delete();
            TenantProduct::whereIn('id', $productIds)->delete();
        }

        TenantProductCategory::where('tenant_id', $tenant->id)->delete();
        TenantProductCollection::where('tenant_id', $tenant->id)->delete();
        TenantMarketingSection::where('tenant_id', $tenant->id)->delete();
    }

    private function prepareAssets(int $tenantId): array
    {
        $sources = [
            'hero' => ['Fashion storefront hero', 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1800&q=80'],
            'tshirt' => ['Men essential T-shirt', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=1200&q=80'],
            'dress' => ['Women midi dress', 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1200&q=80'],
            'wallet' => ['Leather wallet', 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=1200&q=80'],
            'sunglasses' => ['Acetate sunglasses', 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=1200&q=80'],
            'coat' => ['Tailored coat', 'https://images.unsplash.com/photo-1543076447-215ad9ba6923?auto=format&fit=crop&w=1200&q=80'],
            'bag' => ['Crossbody bag', 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?auto=format&fit=crop&w=1200&q=80'],
            'shirt' => ['Oxford shirt', 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=1200&q=80'],
        ];

        $paths = [];
        foreach ($sources as $key => [$label, $url]) {
            $paths[$key] = $this->storeImage($tenantId, $key, $label, $url);
        }

        return $paths;
    }

    private function storeImage(int $tenantId, string $key, string $label, string $url): string
    {
        $targetDir = storage_path("app/public/tenants/{$tenantId}/fashion-demo");
        File::ensureDirectoryExists($targetDir);

        $target = "{$targetDir}/{$key}.jpg";
        $relative = "tenants/{$tenantId}/fashion-demo/{$key}.jpg";

        try {
            $response = Http::timeout(20)->get($url);
            if ($response->successful() && strlen($response->body()) > 1000) {
                File::put($target, $response->body());
                return $relative;
            }
        } catch (Throwable) {
            //
        }

        $svgTarget = "{$targetDir}/{$key}.svg";
        File::put($svgTarget, $this->fallbackSvg($label, $key));

        return "tenants/{$tenantId}/fashion-demo/{$key}.svg";
    }

    private function fallbackSvg(string $label, string $key): string
    {
        $safeLabel = e($label);
        $colors = [
            'hero' => ['#111827', '#b45309'],
            'tshirt' => ['#e5e7eb', '#111827'],
            'dress' => ['#fce7f3', '#be185d'],
            'wallet' => ['#fef3c7', '#92400e'],
            'sunglasses' => ['#dbeafe', '#1e3a8a'],
            'coat' => ['#ede9fe', '#5b21b6'],
            'bag' => ['#dcfce7', '#166534'],
            'shirt' => ['#ecfeff', '#155e75'],
        ][$key] ?? ['#f8fafc', '#334155'];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="1500" viewBox="0 0 1200 1500">
  <defs>
    <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="{$colors[0]}"/>
      <stop offset="100%" stop-color="{$colors[1]}"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="1500" fill="url(#g)"/>
  <rect x="150" y="210" width="900" height="1080" rx="72" fill="rgba(255,255,255,.18)" stroke="rgba(255,255,255,.45)" stroke-width="4"/>
  <text x="600" y="705" text-anchor="middle" font-family="Arial, sans-serif" font-size="58" font-weight="700" fill="#ffffff">{$safeLabel}</text>
  <text x="600" y="790" text-anchor="middle" font-family="Arial, sans-serif" font-size="28" fill="rgba(255,255,255,.82)">ModeHaus demo product</text>
</svg>
SVG;
    }

    private function categories(): array
    {
        return [
            ['slug' => 'men', 'name' => 'Men', 'description' => 'T-shirts, shirts, coats, and everyday staples.', 'image' => 'tshirt'],
            ['slug' => 'women', 'name' => 'Women', 'description' => 'Dresses, layers, and modern silhouettes.', 'image' => 'dress'],
            ['slug' => 'accessories', 'name' => 'Accessories', 'description' => 'Wallets, sunglasses, bags, and finishing pieces.', 'image' => 'wallet'],
        ];
    }

    private function collections(): array
    {
        return [
            ['slug' => 'new-arrivals', 'name' => 'New Arrivals', 'description' => 'Fresh products for a modern fashion launch.', 'image' => 'hero'],
            ['slug' => 'office-edit', 'name' => 'Office Edit', 'description' => 'Clean apparel and accessories for workdays.', 'image' => 'coat'],
            ['slug' => 'weekend-accessories', 'name' => 'Weekend Accessories', 'description' => 'Wallets, sunglasses, and bags for daily carry.', 'image' => 'bag'],
        ];
    }

    private function products(): array
    {
        return [
            ['slug' => 'essential-oversized-t-shirt', 'name' => 'Essential Oversized T-Shirt', 'category' => 'men', 'collections' => ['new-arrivals'], 'price' => 1299, 'stock' => 64, 'image' => 'tshirt', 'gallery' => ['shirt', 'coat'], 'description' => 'A heavyweight cotton T-shirt with relaxed fit, clean neckline, and everyday styling.', 'material' => '240 GSM cotton jersey', 'care' => 'Machine wash cold. Do not bleach.', 'featured' => true],
            ['slug' => 'linen-midi-dress', 'name' => 'Linen Midi Dress', 'category' => 'women', 'collections' => ['new-arrivals', 'office-edit'], 'price' => 3499, 'stock' => 28, 'image' => 'dress', 'gallery' => ['hero', 'bag'], 'description' => 'A breathable midi dress designed for day-to-evening wear with a flattering easy silhouette.', 'material' => 'Linen blend', 'care' => 'Gentle wash. Steam or warm iron.', 'featured' => true],
            ['slug' => 'classic-leather-wallet', 'name' => 'Classic Leather Wallet', 'category' => 'accessories', 'collections' => ['weekend-accessories'], 'price' => 1899, 'stock' => 45, 'image' => 'wallet', 'gallery' => ['sunglasses', 'bag'], 'description' => 'A slim leather wallet with card slots, cash pocket, and clean stitched finish.', 'material' => 'Full-grain leather', 'care' => 'Wipe with a soft dry cloth.', 'featured' => true],
            ['slug' => 'acetate-sunglasses', 'name' => 'Acetate Sunglasses', 'category' => 'accessories', 'collections' => ['new-arrivals', 'weekend-accessories'], 'price' => 1499, 'stock' => 38, 'image' => 'sunglasses', 'gallery' => ['wallet', 'hero'], 'description' => 'Lightweight acetate sunglasses with UV-protective lenses and everyday styling.', 'material' => 'Acetate frame, UV lenses', 'care' => 'Store in case. Clean with lens cloth.', 'featured' => true],
            ['slug' => 'tailored-wool-coat', 'name' => 'Tailored Wool Coat', 'category' => 'men', 'collections' => ['office-edit'], 'price' => 6999, 'stock' => 14, 'image' => 'coat', 'gallery' => ['shirt', 'tshirt'], 'description' => 'A structured coat for smart layering, designed with clean lines and polished finishing.', 'material' => 'Wool blend', 'care' => 'Dry clean only.', 'featured' => false],
            ['slug' => 'everyday-crossbody-bag', 'name' => 'Everyday Crossbody Bag', 'category' => 'accessories', 'collections' => ['weekend-accessories', 'office-edit'], 'price' => 2799, 'stock' => 23, 'image' => 'bag', 'gallery' => ['wallet', 'sunglasses'], 'description' => 'A compact crossbody bag with adjustable strap, organized pockets, and premium finish.', 'material' => 'Vegan leather', 'care' => 'Spot clean only.', 'featured' => false],
            ['slug' => 'crisp-oxford-shirt', 'name' => 'Crisp Oxford Shirt', 'category' => 'men', 'collections' => ['office-edit'], 'price' => 2199, 'stock' => 40, 'image' => 'shirt', 'gallery' => ['coat', 'tshirt'], 'description' => 'A versatile button-down shirt for office, casual, and weekend styling.', 'material' => 'Cotton oxford', 'care' => 'Machine wash cold and hang dry.', 'featured' => true],
        ];
    }

    private function marketingSections(): array
    {
        return [
            ['title' => 'New Season Picks', 'type' => 'products', 'display_style' => 'grid', 'items_per_row' => 4, 'filter_type' => 'featured', 'filter_value' => null],
            ['title' => 'Shop by Style', 'type' => 'collections', 'display_style' => 'grid', 'items_per_row' => 3, 'filter_type' => 'collection', 'filter_value' => null],
        ];
    }
};
