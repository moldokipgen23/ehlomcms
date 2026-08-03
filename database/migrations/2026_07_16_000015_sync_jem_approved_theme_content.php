<?php

use App\Models\Tenant;
use App\Models\TenantMarketingSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $tenant = Tenant::find(21);

        if (!$tenant) {
            return;
        }

        $settings = array_merge($tenant->theme_settings ?? [], [
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
            'jem_hero_image' => 'tenants/21/jemdesign-demo/Screenshot_20260630-015444.png',
            'jem_story_image' => 'tenants/21/jemdesign-demo/WhatsApp Image 2026-06-30 at 01.58.32.jpeg',
            'jem_founder_image' => 'tenants/21/jemdesign-demo/Screenshot_20260630-015627.png',
            'jem_detail_image' => 'tenants/21/jemdesign-demo/Screenshot_20260630-015615.png',
            'jem_accent_image' => 'tenants/21/jemdesign-demo/Screenshot_20260630-015604.png',
        ]);

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
            'jem_preloader',
        ])));

        $tenant->update([
            'template_id' => 'jemdesign',
            'site_type' => 'shopping',
            'banner_image' => $tenant->banner_image ?: 'tenants/21/jemdesign-demo/Screenshot_20260630-015444.png',
            'about_text' => $tenant->about_text ?: 'Traditional Kuki-Zo weave motifs reimagined for contemporary wardrobes. Jem Designs & Co. brings heritage craft into modern silhouettes.',
            'theme_settings' => $settings,
            'modules' => $modules,
        ]);

        foreach ($this->sections() as $index => $section) {
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
        TenantMarketingSection::where('tenant_id', 21)
            ->whereIn('title', ['Featured Pieces', 'Signature Series'])
            ->delete();
    }

    private function sections(): array
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
};
