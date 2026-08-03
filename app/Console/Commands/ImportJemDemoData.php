<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantMarketingSection;
use App\Models\TenantProduct;
use App\Models\TenantProductCategory;
use App\Models\TenantProductCollection;
use App\Models\TenantProductColor;
use App\Models\TenantProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportJemDemoData extends Command
{
    protected $signature = 'jem:import-demo {tenant=21} {--force : Replace existing Jem demo records for this tenant}';

    protected $description = 'Import the approved Jem Designs demo storefront products, collections, and imagery into a tenant.';

    public function handle(): int
    {
        $tenant = Tenant::find((int) $this->argument('tenant'));

        if (!$tenant) {
            $this->error('Tenant not found.');
            return self::FAILURE;
        }

        if ($this->option('force')) {
            TenantProduct::where('tenant_id', $tenant->id)
                ->whereIn('slug', collect($this->products())->pluck('slug'))
                ->each(fn (TenantProduct $product) => $product->delete());
        }

        $assetMap = [
            'hero' => 'Screenshot_20260630-015444.png',
            'story' => 'WhatsApp Image 2026-06-30 at 01.58.32.jpeg',
            'shirt' => 'Screenshot_20260630-015645.png',
            'shawl' => 'g.jpeg',
            'tote' => 'Screenshot_20260630-015502.png',
            'skirt' => 'Screenshot_20260630-015538.png',
            'dress' => 'Screenshot_20260630-015552.png',
            'jacket' => 'Screenshot_20260630-015627.png',
            'clutch' => 'Screenshot_20260630-015604.png',
            'linen' => 'Screenshot_20260630-015615.png',
        ];

        $copied = [];
        foreach ($assetMap as $key => $file) {
            $copied[$key] = $this->copyAsset($tenant->id, $file);
        }

        $categories = [];
        foreach ($this->categories() as $index => $data) {
            $categories[$data['slug']] = TenantProductCategory::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'image' => $copied[$data['image']] ?? null,
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
                    'cover_image' => $copied[$data['image']] ?? null,
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
                    'type' => 'variable',
                    'price' => $data['price'],
                    'photo' => $copied[$data['image']] ?? null,
                    'cover_image' => $copied[$data['image']] ?? null,
                    'category' => $categories[$data['category']]?->name,
                    'description' => $data['description'],
                    'sku' => strtoupper(Str::slug($data['slug'], '')),
                    'stock' => $data['stock'],
                    'material' => $data['material'],
                    'care_instructions' => 'Dry clean recommended. Store folded in a cool, dry place.',
                    'heritage_note' => 'Inspired by Kuki-Zo weave motifs and contemporary Northeast Indian craft.',
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

            $gallery = array_values(array_unique(array_merge([$data['image']], $data['gallery'] ?? [])));
            foreach ($gallery as $galleryIndex => $assetKey) {
                if (empty($copied[$assetKey])) {
                    continue;
                }

                TenantProductImage::updateOrCreate(
                    ['tenant_product_id' => $product->id, 'image_path' => $copied[$assetKey]],
                    ['tenant_product_color_id' => null, 'sort_order' => $galleryIndex]
                );
            }

            foreach ($data['colors'] as $colorIndex => $color) {
                $productColor = TenantProductColor::updateOrCreate(
                    ['tenant_product_id' => $product->id, 'color_name' => $color['name']],
                    ['hex_code' => $color['hex'], 'sort_order' => $colorIndex]
                );

                TenantProductImage::updateOrCreate(
                    ['tenant_product_id' => $product->id, 'tenant_product_color_id' => $productColor->id, 'image_path' => $copied[$data['image']]],
                    ['sort_order' => $colorIndex]
                );
            }
        }

        $modules = array_values(array_unique(array_merge($tenant->modules ?? [], [
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
        ])));

        $tenant->update([
            'template_id' => 'jemdesign',
            'site_type' => 'shopping',
            'modules' => $modules,
            'banner_image' => $tenant->banner_image ?: $copied['hero'],
            'about_text' => $tenant->about_text ?: 'Traditional Kuki-Zo weave motifs reimagined for contemporary wardrobes. Jem Designs & Co. brings heritage craft into modern silhouettes.',
            'theme_settings' => array_merge($tenant->theme_settings ?? [], $this->themeSettings($copied)),
        ]);

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

        $this->info("Imported Jem demo data for tenant {$tenant->id}: {$tenant->name}");
        $this->info('Products: ' . TenantProduct::where('tenant_id', $tenant->id)->count());
        $this->info('Collections: ' . TenantProductCollection::where('tenant_id', $tenant->id)->count());

        return self::SUCCESS;
    }

    private function copyAsset(int $tenantId, string $file): string
    {
        $source = public_path('images/jemdesign/' . $file);
        $targetDir = storage_path("app/public/tenants/{$tenantId}/jemdesign-demo");
        $target = $targetDir . '/' . $file;

        File::ensureDirectoryExists($targetDir);

        if (File::exists($source) && !File::exists($target)) {
            File::copy($source, $target);
        }

        return "tenants/{$tenantId}/jemdesign-demo/{$file}";
    }

    private function categories(): array
    {
        return [
            ['slug' => 'womens', 'name' => "Women's", 'description' => 'Shawls, stoles, dresses, and modern heritage silhouettes.', 'image' => 'shawl'],
            ['slug' => 'mens', 'name' => "Men's", 'description' => 'Heritage shirts, linen staples, and refined everyday pieces.', 'image' => 'shirt'],
            ['slug' => 'accessories', 'name' => 'Accessories', 'description' => 'Bags, clutches, woven details, and finishing pieces.', 'image' => 'tote'],
        ];
    }

    private function themeSettings(array $copied): array
    {
        return [
            'accent_color' => '#e8a930',
            'store_hero_eyebrow' => 'Jem Designs & Co.',
            'store_hero_title' => "Where Heritage\nMeets the\nModern Silhouette",
            'store_hero_subtitle' => 'Traditional Kuki-Zo weave motifs reimagined for contemporary wardrobes.',
            'store_primary_cta' => 'Discover the Collection',
            'store_secondary_cta' => 'Explore Collections',
            'about_title' => "Woven With\nIntention",
            'store_highlight_1' => 'Handwoven Textiles',
            'store_highlight_2' => 'Heritage Motifs',
            'store_highlight_3' => 'Northeast India',
            'featured_products_title' => 'Modern Heritage Essentials',
            'collections_title' => 'Shop by Collection',
            'shipping_promise' => 'Carefully packed orders',
            'quality_promise' => 'Premium handcrafted design',
            'support_promise' => 'WhatsApp support available',
            'footer_tagline' => 'A seamless blend of heritage and modern design.',
            'footer_about' => 'Traditional Kuki-Zo tribal weave motifs reimagined for contemporary wardrobes.',
            'instagram_url' => 'https://www.instagram.com/jem.designsandco',
            'jem_preloader_enabled' => '1',
            'jem_hero_image' => $copied['hero'] ?? null,
            'jem_story_image' => $copied['story'] ?? null,
            'jem_founder_image' => $copied['jacket'] ?? null,
            'jem_detail_image' => $copied['linen'] ?? null,
            'jem_accent_image' => $copied['clutch'] ?? null,
        ];
    }

    private function marketingSections(): array
    {
        return [
            [
                'title' => 'Featured Pieces',
                'type' => 'products',
                'display_style' => 'grid',
                'items_per_row' => 4,
                'filter_type' => 'featured',
                'filter_value' => null,
            ],
            [
                'title' => 'Signature Series',
                'type' => 'collections',
                'display_style' => 'grid',
                'items_per_row' => 2,
                'filter_type' => 'collection',
                'filter_value' => null,
            ],
        ];
    }

    private function collections(): array
    {
        return [
            ['slug' => 'signature-series', 'name' => 'Signature Series', 'description' => 'Modern silhouettes carrying Jem heritage motifs.', 'image' => 'shirt'],
            ['slug' => 'heredit-blossoms', 'name' => 'HerEDIT & Blossoms', 'description' => 'Elegant shawls, stoles, and women-led craft stories.', 'image' => 'shawl'],
            ['slug' => 'everyday-craft', 'name' => 'Everyday Craft', 'description' => 'Accessible handcrafted pieces for daily wear.', 'image' => 'tote'],
        ];
    }

    private function products(): array
    {
        return [
            ['slug' => 'heritage-weave-shirt', 'name' => 'Heritage Weave Shirt', 'price' => 2499, 'category' => 'mens', 'collections' => ['signature-series'], 'image' => 'shirt', 'gallery' => ['linen', 'jacket'], 'stock' => 18, 'material' => 'Handwoven cotton', 'featured' => true, 'description' => 'Handwoven cotton shirt featuring traditional Kuki-Zo weave patterns in indigo and natural tones.', 'colors' => [['name' => 'Indigo', 'hex' => '#1B3A5C'], ['name' => 'Natural', 'hex' => '#D4C5A9']]],
            ['slug' => 'heritage-shawl-wrap', 'name' => 'Heritage Shawl Wrap', 'price' => 3299, 'category' => 'womens', 'collections' => ['heredit-blossoms', 'signature-series'], 'image' => 'shawl', 'gallery' => ['dress', 'skirt'], 'stock' => 12, 'material' => 'Handwoven shawl textile', 'featured' => true, 'description' => 'Luxurious handwoven shawl wrap with intricate tribal patterns for ceremonial and everyday styling.', 'colors' => [['name' => 'Rust', 'hex' => '#B7410E'], ['name' => 'Forest', 'hex' => '#228B22']]],
            ['slug' => 'tribal-motif-tote', 'name' => 'Tribal Motif Tote', 'price' => 1299, 'category' => 'accessories', 'collections' => ['everyday-craft'], 'image' => 'tote', 'gallery' => ['clutch', 'shawl'], 'stock' => 24, 'material' => 'Canvas with embroidered motif', 'featured' => true, 'description' => 'Handcrafted tote with embroidered tribal motifs, leather-style handles, and a spacious everyday carry shape.', 'colors' => [['name' => 'Natural', 'hex' => '#E8DCC8'], ['name' => 'Indigo', 'hex' => '#1B3A5C']]],
            ['slug' => 'woven-stripe-midi', 'name' => 'Woven Stripe Midi', 'price' => 1899, 'category' => 'womens', 'collections' => ['heredit-blossoms'], 'image' => 'skirt', 'gallery' => ['dress', 'shawl'], 'stock' => 15, 'material' => 'Cotton blend weave', 'featured' => true, 'description' => 'Elegant midi skirt with a handwoven stripe rhythm and a soft flowing silhouette.', 'colors' => [['name' => 'Earth', 'hex' => '#8B7355'], ['name' => 'Indigo', 'hex' => '#2C4A6E']]],
            ['slug' => 'tribal-print-dress', 'name' => 'Tribal Print Dress', 'price' => 2799, 'category' => 'womens', 'collections' => ['heredit-blossoms', 'signature-series'], 'image' => 'dress', 'gallery' => ['skirt', 'shawl'], 'stock' => 10, 'material' => 'Organic cotton', 'featured' => true, 'description' => 'Flowing A-line dress with a heritage-inspired all-over print and contemporary fit.', 'colors' => [['name' => 'Indigo', 'hex' => '#1B3A5C'], ['name' => 'Terracotta', 'hex' => '#E2725B']]],
            ['slug' => 'heritage-jacket', 'name' => 'Heritage Jacket', 'price' => 4499, 'category' => 'womens', 'collections' => ['signature-series'], 'image' => 'jacket', 'gallery' => ['shirt', 'linen'], 'stock' => 8, 'material' => 'Structured cotton with woven panels', 'featured' => true, 'description' => 'Structured jacket with handwoven panels where modern tailoring meets traditional craft.', 'colors' => [['name' => 'Midnight', 'hex' => '#191970'], ['name' => 'Camel', 'hex' => '#C19A6B']]],
            ['slug' => 'embroidered-clutch', 'name' => 'Embroidered Clutch', 'price' => 999, 'category' => 'accessories', 'collections' => ['everyday-craft'], 'image' => 'clutch', 'gallery' => ['tote', 'shawl'], 'stock' => 30, 'material' => 'Hand embroidered textile', 'featured' => false, 'description' => 'Hand-embroidered clutch bag with traditional motifs and a polished evening shape.', 'colors' => [['name' => 'Maroon', 'hex' => '#800000'], ['name' => 'Black', 'hex' => '#1C1C1C']]],
            ['slug' => 'handloom-linen-shirt', 'name' => 'Handloom Linen Shirt', 'price' => 2199, 'category' => 'mens', 'collections' => ['everyday-craft'], 'image' => 'linen', 'gallery' => ['shirt', 'jacket'], 'stock' => 16, 'material' => 'Handloom linen cotton', 'featured' => false, 'description' => 'Relaxed-fit linen shirt with handloom texture, made for warm weather and quiet elegance.', 'colors' => [['name' => 'White', 'hex' => '#F5F5DC'], ['name' => 'Sand', 'hex' => '#C2B280']]],
        ];
    }
}
