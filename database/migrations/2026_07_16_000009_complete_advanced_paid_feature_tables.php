<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tenant_subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_product_id')->nullable()->constrained('tenant_products')->nullOnDelete();
            $table->string('name');
            $table->string('interval')->default('monthly');
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tenant_loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_customer_id')->nullable()->constrained('tenant_customers')->nullOnDelete();
            $table->foreignId('tenant_order_id')->nullable()->constrained('tenant_orders')->nullOnDelete();
            $table->integer('points');
            $table->string('type')->default('earned');
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('session_id');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->json('cart_data')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamp('recovered_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'session_id']);
        });

        Schema::table('tenant_products', function (Blueprint $table) {
            $table->foreignId('tenant_vendor_id')->nullable()->after('tenant_id')->constrained('tenant_vendors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenant_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_vendor_id');
        });
        Schema::dropIfExists('tenant_abandoned_carts');
        Schema::dropIfExists('tenant_loyalty_transactions');
        Schema::dropIfExists('tenant_subscription_plans');
        Schema::dropIfExists('tenant_vendors');
    }
};
