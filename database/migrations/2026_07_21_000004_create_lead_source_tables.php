<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver');
            $table->string('base_url')->nullable();
            $table->text('credentials')->nullable();
            $table->json('settings')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->unsignedInteger('last_imported_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('lead_source_id')->nullable()->after('source')->constrained('lead_sources')->nullOnDelete();
            $table->string('external_id')->nullable()->after('lead_source_id');
            $table->string('website_url')->nullable()->after('phone');
            $table->json('external_metadata')->nullable()->after('notes');
            $table->timestamp('last_synced_at')->nullable()->after('external_metadata');
            $table->index(['lead_source_id', 'external_id'], 'lead_source_external_key');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('lead_source_external_key');
            $table->dropConstrainedForeignId('lead_source_id');
            $table->dropColumn(['external_id', 'website_url', 'external_metadata', 'last_synced_at']);
        });

        Schema::dropIfExists('lead_sources');
    }
};
