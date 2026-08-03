<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_type_modules', function (Blueprint $table) {
            if (!Schema::hasColumn('business_type_modules', 'billing_cycle')) {
                $table->string('billing_cycle')->default('one_time')->after('price');
            }
        });

        DB::table('business_type_modules')
            ->where('status', 'paid')
            ->whereNull('billing_cycle')
            ->update(['billing_cycle' => 'one_time']);

        DB::table('business_type_modules')
            ->where('status', 'paid')
            ->where('business_type', 'shopping')
            ->whereIn('module_key', [
                'variants',
                'product_videos',
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
                'jem_preloader',
                'pos_integration',
            ])
            ->update(['billing_cycle' => 'one_time']);

        // These are platform/service marketplace add-ons, not business-module
        // feature products. Keep them in Add-on Marketplace.
        DB::table('addon_products')
            ->whereIn('key', ['whatsapp_automation', 'ai_agent', 'analytics_pro', 'email_marketing'])
            ->update(['module_key' => null]);
    }

    public function down(): void
    {
        Schema::table('business_type_modules', function (Blueprint $table) {
            if (Schema::hasColumn('business_type_modules', 'billing_cycle')) {
                $table->dropColumn('billing_cycle');
            }
        });
    }
};
