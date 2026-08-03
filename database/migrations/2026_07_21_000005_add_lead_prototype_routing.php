<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedTinyInteger('lead_score')->nullable()->after('status');
            $table->json('score_reasons')->nullable()->after('lead_score');
            $table->string('recommended_offer')->nullable()->after('score_reasons');
            $table->string('prototype_key')->nullable()->after('recommended_offer');
            $table->string('prototype_url')->nullable()->after('prototype_key');
            $table->index(['prototype_key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['prototype_key', 'status']);
            $table->dropColumn(['lead_score', 'score_reasons', 'recommended_offer', 'prototype_key', 'prototype_url']);
        });
    }
};
