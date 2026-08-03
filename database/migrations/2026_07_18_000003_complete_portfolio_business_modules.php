<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_business_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->string('image')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['tenant_id', 'type', 'is_active']);
            $table->unique(['tenant_id', 'type', 'slug']);
        });

        Schema::create('tenant_business_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40)->default('enquiry');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->json('meta')->nullable();
            $table->string('status', 30)->default('new');
            $table->timestamps();
            $table->index(['tenant_id', 'type', 'status']);
        });

        Schema::create('tenant_newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->unique(['tenant_id', 'email']);
        });

        foreach (['content', 'services', 'testimonials', 'blog', 'case_studies', 'team', 'careers', 'newsletter', 'enquiries'] as $key) {
            DB::table('business_type_modules')->updateOrInsert(
                ['business_type' => 'business', 'module_key' => $key],
                ['status' => 'free', 'price' => null, 'billing_cycle' => 'one_time', 'updated_at' => now(), 'created_at' => now()]
            );
        }

        DB::table('tenants')->where('site_type', 'business')->orderBy('id')->chunkById(100, function ($tenants) {
            $defaults = ['content', 'services', 'testimonials', 'blog', 'case_studies', 'team', 'careers', 'newsletter', 'enquiries'];
            foreach ($tenants as $tenant) {
                $modules = json_decode($tenant->modules ?? '[]', true) ?: [];
                DB::table('tenants')->where('id', $tenant->id)->update([
                    'modules' => json_encode(array_values(array_unique(array_merge($modules, $defaults)))),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_newsletter_subscribers');
        Schema::dropIfExists('tenant_business_enquiries');
        Schema::dropIfExists('tenant_business_items');
    }
};
