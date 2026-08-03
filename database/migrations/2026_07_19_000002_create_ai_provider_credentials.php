<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_provider_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('provider');
            $table->text('api_key');
            $table->string('base_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['provider', 'is_active']);
        });

        Schema::table('ai_agents', function (Blueprint $table) {
            $table->foreignId('provider_credential_id')
                ->nullable()
                ->after('created_by')
                ->constrained('ai_provider_credentials')
                ->nullOnDelete();
            $table->string('provider')->default('gemini')->after('role');
            $table->string('model')->nullable()->after('provider');
            $table->string('fallback_provider')->nullable()->after('model');
            $table->string('fallback_model')->nullable()->after('fallback_provider');
        });
    }

    public function down(): void
    {
        Schema::table('ai_agents', function (Blueprint $table) {
            $table->dropForeign(['provider_credential_id']);
            $table->dropColumn(['provider_credential_id', 'provider', 'model', 'fallback_provider', 'fallback_model']);
        });

        Schema::dropIfExists('ai_provider_credentials');
    }
};
