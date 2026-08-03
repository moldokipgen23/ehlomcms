<?php

use App\Models\AddonProduct;
use App\Models\BusinessTypeModule;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $addons = [
            'product_collections' => ['module_key' => 'product_collections', 'name' => 'Collections', 'price' => 799, 'icon' => 'ti-folders', 'description' => 'Reusable product collections such as Signature Series, New Arrivals, and seasonal edits.'],
            'product_options' => ['module_key' => 'variants', 'name' => 'Colors, Sizes & Variants', 'price' => 1499, 'icon' => 'ti-list-tree', 'description' => 'Color swatches, sizes, variant SKU, variant stock, and variant price overrides.'],
            'inventory' => ['module_key' => 'inventory', 'name' => 'Inventory', 'price' => 999, 'icon' => 'ti-packages', 'description' => 'Product and variant stock, low-stock indicators, and checkout stock reduction.'],
            'product_videos' => ['module_key' => 'product_videos', 'name' => 'Product Videos', 'price' => 799, 'icon' => 'ti-video', 'description' => 'Upload product videos for product detail pages and future premium themes.'],
            'search_filters' => ['module_key' => 'search_filters', 'name' => 'Search & Filters', 'price' => 999, 'icon' => 'ti-filter', 'description' => 'Search products, filter by category or collection, and sort the catalog.'],
            'marketing_sections' => ['module_key' => 'marketing_sections', 'name' => 'Marketing Sections', 'price' => 1299, 'icon' => 'ti-layout-grid-add', 'description' => 'Homepage product sections such as trending, new arrivals, editor picks, and testimonials.'],
            'wishlist' => ['module_key' => 'wishlist', 'name' => 'Wishlist', 'price' => 499, 'icon' => 'ti-heart', 'description' => 'Let shoppers save products for later and return to buy.'],
            'customer_accounts' => ['module_key' => 'customer_accounts', 'name' => 'Customer Accounts', 'price' => 1499, 'icon' => 'ti-user-circle', 'description' => 'Customer login, saved profile, saved addresses, and order history.'],
            'razorpay_gateway' => ['module_key' => 'razorpay_gateway', 'name' => 'Razorpay Gateway', 'price' => 999, 'icon' => 'ti-credit-card-pay', 'description' => 'Accept cards, UPI, netbanking, and wallet payments with the store owner Razorpay account.'],
            'reviews' => ['module_key' => 'reviews', 'name' => 'Product Reviews', 'price' => 799, 'icon' => 'ti-star', 'description' => 'Customer ratings and reviews on product pages.'],
            'coupons' => ['module_key' => 'coupons', 'name' => 'Coupons & Discounts', 'price' => 1299, 'icon' => 'ti-ticket', 'description' => 'Create discount codes, promotional offers, and campaign coupons.'],
            'shipping_rules' => ['module_key' => 'shipping_rules', 'name' => 'Shipping Rules', 'price' => 999, 'icon' => 'ti-truck-delivery', 'description' => 'Delivery fee rules, free shipping thresholds, and pincode rules.'],
            'gst_invoice' => ['module_key' => 'gst_invoice', 'name' => 'GST Invoice', 'price' => 1499, 'icon' => 'ti-file-invoice', 'description' => 'Tax calculation and printable/downloadable GST invoices.'],
            'abandoned_cart' => ['module_key' => 'abandoned_cart', 'name' => 'Abandoned Cart Recovery', 'price' => 1499, 'icon' => 'ti-shopping-cart-exclamation', 'description' => 'Recover incomplete carts with WhatsApp/email reminders.'],
            'loyalty_rewards' => ['module_key' => 'loyalty_rewards', 'name' => 'Loyalty & Rewards', 'price' => 1999, 'icon' => 'ti-gift', 'description' => 'Points, reward rules, referrals, and repeat purchase incentives.'],
            'subscription_products' => ['module_key' => 'subscription_products', 'name' => 'Subscription Products', 'price' => 2999, 'icon' => 'ti-refresh', 'description' => 'Sell recurring subscriptions and repeat-order products.'],
            'advanced_analytics' => ['module_key' => 'advanced_analytics', 'name' => 'Advanced Store Analytics', 'price' => 1999, 'icon' => 'ti-chart-histogram', 'description' => 'Sales trends, product performance, conversion reports, and customer insights.'],
            'seo_booster' => ['module_key' => 'seo_booster', 'name' => 'SEO Booster', 'price' => 999, 'icon' => 'ti-seo', 'description' => 'Product SEO metadata, social preview tags, sitemap, and search snippets.'],
            'bulk_import_export' => ['module_key' => 'bulk_import_export', 'name' => 'Bulk Import / Export', 'price' => 1499, 'icon' => 'ti-file-spreadsheet', 'description' => 'Import and export products, variants, prices, and inventory using spreadsheets.'],
            'multi_vendor' => ['module_key' => 'multi_vendor', 'name' => 'Multi-Vendor Marketplace', 'price' => 4999, 'icon' => 'ti-building-store', 'description' => 'Multiple sellers with seller dashboards, commissions, and payout tracking.'],
            'pos_integration' => ['module_key' => 'pos_integration', 'name' => 'POS Integration', 'price' => 4999, 'icon' => 'ti-device-desktop', 'description' => 'Sync store products and inventory with supported POS systems.'],
        ];

        foreach ($addons as $key => $addon) {
            AddonProduct::updateOrCreate(
                ['key' => $key],
                [
                    'module_key' => $addon['module_key'],
                    'name' => $addon['name'],
                    'description' => $addon['description'],
                    'price' => $addon['price'],
                    'icon' => $addon['icon'],
                    'active' => true,
                    'business_types' => ['shopping'],
                ],
            );

            BusinessTypeModule::updateOrCreate(
                ['business_type' => 'shopping', 'module_key' => $addon['module_key']],
                ['status' => 'paid', 'price' => $addon['price']],
            );
        }

        AddonProduct::where('key', 'whatsapp_automation')->update([
            'module_key' => 'whatsapp_automation',
            'business_types' => ['shopping', 'restaurant'],
            'price' => 499,
            'active' => true,
        ]);
    }

    public function down(): void
    {
        $keys = [
            'wishlist',
            'customer_accounts',
            'razorpay_gateway',
            'reviews',
            'coupons',
            'shipping_rules',
            'gst_invoice',
            'abandoned_cart',
            'loyalty_rewards',
            'subscription_products',
            'advanced_analytics',
            'seo_booster',
            'bulk_import_export',
            'multi_vendor',
            'pos_integration',
        ];

        AddonProduct::whereIn('key', $keys)->delete();
        BusinessTypeModule::where('business_type', 'shopping')->whereIn('module_key', $keys)->delete();
    }
};
