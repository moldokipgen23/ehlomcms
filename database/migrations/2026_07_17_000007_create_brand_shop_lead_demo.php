<?php

use App\Models\Tenant;
use App\Models\TenantMarketingSection;
use App\Models\TenantProduct;
use App\Models\TenantProductCategory;
use App\Models\TenantProductCollection;
use App\Models\TenantProductImage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tenant = Tenant::updateOrCreate(
            ['subdomain' => 'brandshopdemo'],
            [
                'name' => 'Luma & Co',
                'site_type' => 'shopping',
                'template_id' => 'brandshop',
                'status' => 'active',
                'plan' => 'Demo',
                'contact_email' => 'hello@luma-demo.test',
                'contact_phone' => '+91 98765 43210',
                'whatsapp_number' => '919876543210',
                'contact_address' => 'Bandra West, Mumbai, India',
                'contact_hours' => 'Mon-Sat, 10:00 AM - 7:00 PM',
                'action_type' => 'whatsapp',
                'about_text' => 'Luma & Co is a modern lifestyle store demo built to show how a real Ehlom ecommerce client can sell curated products, manage catalog content, accept orders, publish policies, and launch a polished storefront from one dashboard.',
                'modules' => [
                    'catalog',
                    'product_categories',
                    'product_gallery',
                    'content',
                    'cart',
                    'checkout',
                    'payments',
                    'orders',
                    'product_collections',
                    'inventory',
                    'search_filters',
                    'marketing_sections',
                    'wishlist',
                    'customer_accounts',
                    'shipping_rules',
                    'seo_booster',
                ],
                'theme_settings' => [
                    'accent_color' => '#0f766e',
                    'store_hero_eyebrow' => 'Lead Demo Store',
                    'store_hero_title' => 'Premium lifestyle essentials, ready to sell online',
                    'store_hero_subtitle' => 'A complete demo storefront with real product pages, collections, cart, WhatsApp checkout, policies, customer account links, and mobile-ready shopping flow.',
                    'store_primary_cta' => 'Shop the Demo',
                    'store_secondary_cta' => 'View Collections',
                    'featured_products_title' => 'Featured Products',
                    'collections_title' => 'Curated Collections',
                    'about_title' => 'Built for Real Store Owners',
                    'store_highlight_1' => 'Product catalog',
                    'store_highlight_2' => 'Cart and checkout',
                    'store_highlight_3' => 'Lead-ready demo data',
                    'shipping_promise' => 'Fast dispatch workflow',
                    'quality_promise' => 'Professional product presentation',
                    'support_promise' => 'WhatsApp order support',
                    'return_policy' => 'Policies and order tracking',
                    'footer_tagline' => 'A professional ecommerce demo powered by Ehlom OS.',
                    'footer_about' => 'Use this demo to show leads how their own branded storefront, product catalog, orders, policies, and dashboard can work together.',
                    'show_about' => true,
                    'show_gallery' => true,
                    'show_contact' => true,
                ],
            ]
        );

        $assets = $this->copyAssets($tenant->id);

        $tenant->update([
            'banner_image' => $assets['hero'] ?? null,
            'logo' => $assets['logo'] ?? null,
        ]);

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
                    'heritage_note' => 'Demo product prepared for lead presentations.',
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
        $tenant = Tenant::where('subdomain', 'brandshopdemo')->first();
        if ($tenant) {
            $tenant->delete();
        }
    }

    private function copyAssets(int $tenantId): array
    {
        $map = [
            'logo' => 'logo.jpg',
            'hero' => 'Screenshot_20260630-015444.png',
            'signature' => 'Screenshot_20260630-015645.png',
            'wrap' => 'g.jpeg',
            'tote' => 'Screenshot_20260630-015502.png',
            'dress' => 'Screenshot_20260630-015552.png',
            'jacket' => 'Screenshot_20260630-015627.png',
            'clutch' => 'Screenshot_20260630-015604.png',
            'linen' => 'Screenshot_20260630-015615.png',
        ];

        $copied = [];
        foreach ($map as $key => $file) {
            $source = public_path('images/jemdesign/' . $file);
            $targetDir = storage_path("app/public/tenants/{$tenantId}/brandshop-demo");
            $target = $targetDir . '/' . $file;

            File::ensureDirectoryExists($targetDir);

            if (File::exists($source) && !File::exists($target)) {
                File::copy($source, $target);
            }

            $copied[$key] = "tenants/{$tenantId}/brandshop-demo/{$file}";
        }

        return $copied;
    }

    private function categories(): array
    {
        return [
            ['slug' => 'apparel', 'name' => 'Apparel', 'description' => 'Ready-to-sell clothing and statement pieces.', 'image' => 'signature'],
            ['slug' => 'accessories', 'name' => 'Accessories', 'description' => 'Bags, clutches, and finishing touches.', 'image' => 'tote'],
            ['slug' => 'home-lifestyle', 'name' => 'Home & Lifestyle', 'description' => 'Curated lifestyle products for gifting and everyday use.', 'image' => 'linen'],
        ];
    }

    private function collections(): array
    {
        return [
            ['slug' => 'launch-edit', 'name' => 'Launch Edit', 'description' => 'The first products a new brand can feature to look complete on launch day.', 'image' => 'hero'],
            ['slug' => 'premium-picks', 'name' => 'Premium Picks', 'description' => 'Higher-value products positioned for gifting, upsell, and brand storytelling.', 'image' => 'jacket'],
            ['slug' => 'everyday-essentials', 'name' => 'Everyday Essentials', 'description' => 'Reliable products that keep the catalog practical and easy to shop.', 'image' => 'linen'],
        ];
    }

    private function products(): array
    {
        return [
            [
                'slug' => 'signature-cotton-shirt',
                'name' => 'Signature Cotton Shirt',
                'category' => 'apparel',
                'collections' => ['launch-edit', 'premium-picks'],
                'price' => 2499,
                'stock' => 32,
                'image' => 'signature',
                'gallery' => ['jacket', 'linen'],
                'description' => 'A polished hero product with clean product details, gallery images, stock, and checkout-ready buying flow.',
                'material' => 'Premium cotton blend',
                'care' => 'Gentle wash or dry clean for longer life.',
                'featured' => true,
            ],
            [
                'slug' => 'soft-shawl-wrap',
                'name' => 'Soft Shawl Wrap',
                'category' => 'apparel',
                'collections' => ['premium-picks'],
                'price' => 3299,
                'stock' => 18,
                'image' => 'wrap',
                'gallery' => ['hero', 'dress'],
                'description' => 'A premium product page example with multiple images, price, stock, cart, and WhatsApp order support.',
                'material' => 'Soft woven textile',
                'care' => 'Dry clean recommended.',
                'featured' => true,
            ],
            [
                'slug' => 'studio-tote-bag',
                'name' => 'Studio Tote Bag',
                'category' => 'accessories',
                'collections' => ['launch-edit', 'everyday-essentials'],
                'price' => 1499,
                'stock' => 44,
                'image' => 'tote',
                'gallery' => ['clutch', 'linen'],
                'description' => 'A useful accessory product for showing category filters, collection grouping, and cart add flow.',
                'material' => 'Canvas and woven detail',
                'care' => 'Spot clean with mild detergent.',
                'featured' => true,
            ],
            [
                'slug' => 'occasion-dress',
                'name' => 'Occasion Dress',
                'category' => 'apparel',
                'collections' => ['premium-picks'],
                'price' => 4599,
                'stock' => 12,
                'image' => 'dress',
                'gallery' => ['signature', 'wrap'],
                'description' => 'A higher-ticket product for showing premium presentation and conversion-focused product pages.',
                'material' => 'Structured occasion fabric',
                'care' => 'Dry clean only.',
                'featured' => false,
            ],
            [
                'slug' => 'minimal-clutch',
                'name' => 'Minimal Clutch',
                'category' => 'accessories',
                'collections' => ['everyday-essentials'],
                'price' => 899,
                'stock' => 60,
                'image' => 'clutch',
                'gallery' => ['tote', 'jacket'],
                'description' => 'A compact add-on product that helps the demo feel like a real catalog, not a single-product page.',
                'material' => 'Textile and metal trim',
                'care' => 'Store in dust bag when not in use.',
                'featured' => false,
            ],
            [
                'slug' => 'linen-home-runner',
                'name' => 'Linen Home Runner',
                'category' => 'home-lifestyle',
                'collections' => ['everyday-essentials'],
                'price' => 1899,
                'stock' => 22,
                'image' => 'linen',
                'gallery' => ['hero', 'clutch'],
                'description' => 'A lifestyle product that shows the theme can work beyond fashion and adapt to multiple shop categories.',
                'material' => 'Textured linen blend',
                'care' => 'Hand wash cold and air dry.',
                'featured' => true,
            ],
        ];
    }

    private function marketingSections(): array
    {
        return [
            ['title' => 'Featured Products', 'type' => 'products', 'display_style' => 'grid', 'items_per_row' => 4, 'filter_type' => 'featured', 'filter_value' => null],
            ['title' => 'Curated Collections', 'type' => 'collections', 'display_style' => 'grid', 'items_per_row' => 3, 'filter_type' => 'collection', 'filter_value' => null],
        ];
    }
};
