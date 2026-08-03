<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('password');
            $table->text('default_address')->nullable();
            $table->string('default_pincode', 20)->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'email']);
        });

        Schema::create('tenant_wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_customer_id')->nullable()->constrained('tenant_customers')->nullOnDelete();
            $table->foreignId('tenant_product_id')->constrained('tenant_products')->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'tenant_customer_id', 'tenant_product_id'], 'tenant_wishlist_customer_unique');
        });

        Schema::create('tenant_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('type')->default('fixed');
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('tenant_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_product_id')->constrained('tenant_products')->cascadeOnDelete();
            $table->foreignId('tenant_customer_id')->nullable()->constrained('tenant_customers')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('tenant_shipping_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('pincode_pattern')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('free_above', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tenant_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('feature_key');
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'feature_key']);
        });

        Schema::table('tenant_orders', function (Blueprint $table) {
            $table->foreignId('tenant_customer_id')->nullable()->after('tenant_id')->constrained('tenant_customers')->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('notes');
            $table->decimal('discount_total', 10, 2)->default(0)->after('coupon_code');
            $table->decimal('shipping_total', 10, 2)->default(0)->after('discount_total');
            $table->decimal('tax_total', 10, 2)->default(0)->after('shipping_total');
            $table->string('invoice_number')->nullable()->after('tax_total');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_customer_id');
            $table->dropColumn(['coupon_code', 'discount_total', 'shipping_total', 'tax_total', 'invoice_number']);
        });
        Schema::dropIfExists('tenant_feature_settings');
        Schema::dropIfExists('tenant_shipping_rules');
        Schema::dropIfExists('tenant_product_reviews');
        Schema::dropIfExists('tenant_coupons');
        Schema::dropIfExists('tenant_wishlist_items');
        Schema::dropIfExists('tenant_customers');
    }
};
