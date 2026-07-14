<?php

use App\Models\AddonProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addon_products', function (Blueprint $table) {
            // Which business types (config/business_types.php keys) this add-on
            // is relevant to. Null/empty = shown under every business type (a
            // cross-business add-on, e.g. WhatsApp). Drives the Free/Paid split
            // on the admin Business Modules page.
            $table->json('business_types')->nullable()->after('active');
        });

        // Backfill the 4 add-ons that existed before this column, matching the
        // original product vision doc's grouping.
        $tags = [
            'whatsapp_automation' => ['shopping', 'restaurant', 'info'],
            'ai_agent' => ['shopping', 'restaurant', 'info'],
            'analytics_pro' => ['shopping', 'restaurant'],
            'email_marketing' => ['shopping', 'restaurant', 'info'],
        ];

        foreach ($tags as $key => $types) {
            AddonProduct::where('key', $key)->update(['business_types' => $types]);
        }
    }

    public function down(): void
    {
        Schema::table('addon_products', function (Blueprint $table) {
            $table->dropColumn('business_types');
        });
    }
};
