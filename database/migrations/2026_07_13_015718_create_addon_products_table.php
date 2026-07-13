<?php

use App\Models\AddonProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addon_products', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('icon')->default('ti-puzzle');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Seed the existing hardcoded config/addons.php entries as real rows
        // with matching keys, so every existing TenantAddon.addon_key
        // reference keeps resolving without a data migration.
        $existing = [
            'whatsapp_automation' => ['name' => 'WhatsApp Automation', 'price' => 499, 'icon' => 'ti-brand-whatsapp',
                'description' => 'Automated WhatsApp order confirmations, shipping updates, and promotional broadcasts to your customers.'],
            'ai_agent' => ['name' => 'AI Agent', 'price' => 999, 'icon' => 'ti-robot',
                'description' => 'AI-powered chatbot for customer inquiries, product recommendations, and 24/7 automated support.'],
            'analytics_pro' => ['name' => 'Analytics Pro', 'price' => 299, 'icon' => 'ti-chart-bar',
                'description' => 'Advanced sales analytics, customer behaviour reports, and inventory forecasting dashboards.'],
            'email_marketing' => ['name' => 'Email Marketing', 'price' => 199, 'icon' => 'ti-mail-star',
                'description' => 'Send targeted email campaigns to your customers with drip sequences and performance tracking.'],
        ];

        foreach ($existing as $key => $data) {
            AddonProduct::create(array_merge($data, ['key' => $key, 'active' => true]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('addon_products');
    }
};
