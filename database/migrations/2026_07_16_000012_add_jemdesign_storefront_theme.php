<?php

use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Theme::updateOrCreate(
            ['key' => 'jemdesign'],
            [
                'name' => 'Jemdesign Storefront',
                'description' => 'Custom shopping storefront theme for Jem Designs with catalog, collections, cart, checkout, trust sections, policies, and customer-facing shop content.',
                'thumbnail' => 'images/themes/shopping/jemdesign-preview.png',
                'base_template' => 'jemdesign',
                'industries' => ['shopping'],
                'default_settings' => [
                    'accent_color' => '#9f6b3f',
                    'store_hero_eyebrow' => 'Jem Designs',
                    'store_hero_title' => 'Heritage, reimagined',
                    'store_primary_cta' => 'Shop Collection',
                    'store_secondary_cta' => 'Explore Collections',
                    'shipping_promise' => 'Carefully packed orders',
                    'quality_promise' => 'Premium handcrafted design',
                    'support_promise' => 'WhatsApp support available',
                ],
                'public' => true,
            ]
        );
    }

    public function down(): void
    {
        Theme::where('key', 'jemdesign')->delete();
    }
};
