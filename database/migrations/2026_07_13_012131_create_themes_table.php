<?php

use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            // The actual Blade view folder this theme renders through
            // (resources/views/tenant-templates/{base_template}/index.blade.php).
            // A theme is a named preset of settings layered on top of one of
            // these; it is not itself a new visual layout.
            $table->string('base_template');
            $table->json('default_settings')->nullable();
            $table->json('industries')->nullable();
            $table->boolean('public')->default(true);
            // Set when this theme was created via "Save as Template" from an
            // existing tenant, for provenance - not used for any logic.
            $table->foreignId('source_tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamps();
        });

        // Seed the two existing hardcoded templates as real rows, matching
        // config/themes.php exactly, so every existing tenant's template_id
        // ('shop' or 'info') keeps resolving without any data migration.
        Theme::create([
            'key' => 'info',
            'name' => 'Info Classic',
            'description' => 'A clean information page with about text, gallery, and contact details. Perfect for churches, NGOs, and portfolio sites.',
            'thumbnail' => 'images/themes/info-preview.png',
            'base_template' => 'info',
            'industries' => ['info'],
            'public' => true,
        ]);

        Theme::create([
            'key' => 'shop',
            'name' => 'Shop Classic',
            'description' => 'A full storefront with product catalog, pricing, and buy buttons. Great for e-commerce and retail.',
            'thumbnail' => 'images/themes/shop-preview.png',
            'base_template' => 'shop',
            'industries' => ['shopping'],
            'public' => true,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
