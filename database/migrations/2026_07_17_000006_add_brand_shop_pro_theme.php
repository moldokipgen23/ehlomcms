<?php

use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Theme::updateOrCreate(
            ['key' => 'brandshop'],
            [
                'name' => 'Brand Shop Pro',
                'description' => 'Modern full ecommerce storefront for single-seller brands: homepage, catalog, product detail gallery, cart, checkout, order confirmation, wishlist, policies, customer account links, trust badges, and conversion-focused store content.',
                'thumbnail' => 'images/themes/shopping/brandshop-preview.png',
                'base_template' => 'brandshop',
                'industries' => ['shopping'],
                'default_settings' => [
                    'accent_color' => '#2563eb',
                    'store_hero_eyebrow' => 'New Collection',
                    'store_hero_title' => 'A modern store built for products that deserve attention',
                    'store_hero_subtitle' => 'Launch a complete ecommerce storefront with catalog, product pages, cart, checkout, order tracking, policies, and customer-ready trust sections.',
                    'store_primary_cta' => 'Shop Collection',
                    'store_secondary_cta' => 'Browse Categories',
                    'featured_products_title' => 'Featured Products',
                    'collections_title' => 'Shop by Collection',
                    'about_title' => 'About the Brand',
                    'store_highlight_1' => 'Curated Products',
                    'store_highlight_2' => 'Secure Checkout',
                    'store_highlight_3' => 'Customer Support',
                    'shipping_promise' => 'Fast dispatch and careful packing',
                    'quality_promise' => 'Quality checked products',
                    'support_promise' => 'WhatsApp support available',
                    'return_policy' => 'Simple return and exchange policy',
                    'footer_tagline' => 'Modern ecommerce powered by Ehlom OS.',
                    'footer_about' => 'A complete online store with products, cart, checkout, order management, policies, and customer-ready shopping flow.',
                    'show_about' => true,
                    'show_gallery' => true,
                    'show_contact' => true,
                ],
                'public' => true,
            ]
        );
    }

    public function down(): void
    {
        Theme::where('key', 'brandshop')->delete();
    }
};
