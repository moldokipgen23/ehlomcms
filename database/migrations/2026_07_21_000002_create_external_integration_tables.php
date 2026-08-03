<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver');
            $table->string('base_url');
            $table->text('credentials')->nullable();
            $table->json('settings')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('external_catalog_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_integration_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('external_type')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('billing_cycle')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['external_integration_id', 'external_id'], 'external_catalog_product_key');
        });

        Schema::create('external_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_integration_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_type')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['external_integration_id', 'external_id'], 'external_account_key');
        });

        Schema::create('external_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_integration_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->foreignId('external_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_product_id')->nullable();
            $table->string('product_name');
            $table->string('status')->default('active');
            $table->string('billing_cycle')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->date('renews_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['external_integration_id', 'external_id'], 'external_subscription_key');
        });

        Schema::create('external_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_integration_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->foreignId('external_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('external_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('local_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('invoice_number')->nullable();
            $table->string('status')->default('unpaid');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['external_integration_id', 'external_id'], 'external_invoice_key');
        });

        Schema::create('external_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_integration_id')->constrained()->cascadeOnDelete();
            $table->string('external_event_id');
            $table->string('event_type');
            $table->json('payload');
            $table->boolean('signature_valid')->default(false);
            $table->string('status')->default('received');
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['external_integration_id', 'external_event_id'], 'external_webhook_event_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_webhook_events');
        Schema::dropIfExists('external_invoices');
        Schema::dropIfExists('external_subscriptions');
        Schema::dropIfExists('external_accounts');
        Schema::dropIfExists('external_catalog_products');
        Schema::dropIfExists('external_integrations');
    }
};
