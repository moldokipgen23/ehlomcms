<?php

use App\Models\AiPrototypeCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_prototype_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('business_type');
            $table->string('theme_key')->nullable();
            $table->string('preview_url')->nullable();
            $table->string('recommended_offer')->nullable();
            $table->json('keywords')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['business_type', 'status']);
        });

        $rows = [
            ['key' => 'school', 'name' => 'School Website Demo', 'business_type' => 'school', 'theme_key' => 'school/eiho', 'preview_url' => 'https://eihoschooldemo.ehlom.com/', 'recommended_offer' => 'School Website Module', 'keywords' => ['school', 'academy', 'college', 'kindergarten', 'nursery', 'public school']],
            ['key' => 'restaurant', 'name' => 'Restaurant Website Demo', 'business_type' => 'restaurant', 'theme_key' => 'restaurant', 'preview_url' => 'https://restaurantdemo.ehlom.com/', 'recommended_offer' => 'Restaurant Website Module', 'keywords' => ['restaurant', 'cafe', 'coffee', 'bakery', 'bistro', 'food', 'dining']],
            ['key' => 'shopping', 'name' => 'Fashion Store Demo', 'business_type' => 'shopping', 'theme_key' => 'brandshop', 'preview_url' => 'https://brandshopdemo.ehlom.com/', 'recommended_offer' => 'Shopping Store Module', 'keywords' => ['shop', 'store', 'boutique', 'clothing', 'fashion', 'retail', 'jewellery', 'accessories']],
            ['key' => 'business', 'name' => 'Business Website Demo', 'business_type' => 'business', 'theme_key' => 'business', 'preview_url' => 'https://portfoliodemo.ehlom.com/', 'recommended_offer' => 'Portfolio / Business Website Module', 'keywords' => ['agency', 'consultant', 'studio', 'service', 'professional', 'lawyer', 'designer']],
        ];

        foreach ($rows as $row) {
            AiPrototypeCatalog::create($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_prototype_catalog');
    }
};
