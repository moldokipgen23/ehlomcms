<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_marketing_sections')) {
        Schema::create('tenant_marketing_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('manual');
            $table->string('display_style')->default('grid');
            $table->unsignedSmallInteger('items_per_row')->default(3);
            $table->string('filter_type')->nullable();
            $table->unsignedBigInteger('filter_value')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('tenant_marketing_section_items')) {
        Schema::create('tenant_marketing_section_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_marketing_section_id');
            $table->string('item_type')->default('product');
            $table->unsignedBigInteger('item_id');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('tenant_marketing_section_id', 'tmsi_section_fk')->references('id')->on('tenant_marketing_sections')->cascadeOnDelete();
        });
        }

        if (!Schema::hasTable('tenant_instagram_posts')) {
        Schema::create('tenant_instagram_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->string('url')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_instagram_posts');
        Schema::dropIfExists('tenant_marketing_section_items');
        Schema::dropIfExists('tenant_marketing_sections');
    }
};
