<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('provider')->default('openai');
            $table->text('api_key')->nullable();
            $table->string('model')->default('gpt-4o-mini');
            $table->boolean('content_enabled')->default(false);
            $table->boolean('assistant_enabled')->default(false);
            $table->boolean('analytics_enabled')->default(false);
            $table->timestamps();

            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
