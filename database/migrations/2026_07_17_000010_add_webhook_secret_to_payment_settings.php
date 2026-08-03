<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payment_settings', 'webhook_secret')) {
            Schema::table('payment_settings', function (Blueprint $table) {
                $table->text('webhook_secret')->nullable()->after('api_secret');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payment_settings', 'webhook_secret')) {
            Schema::table('payment_settings', function (Blueprint $table) {
                $table->dropColumn('webhook_secret');
            });
        }
    }
};
