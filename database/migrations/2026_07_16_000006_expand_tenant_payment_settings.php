<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->boolean('cod_enabled')->default(true)->after('provider');
            $table->boolean('whatsapp_enabled')->default(true)->after('cod_enabled');
            $table->boolean('razorpay_enabled')->default(false)->after('whatsapp_enabled');
            $table->boolean('custom_enabled')->default(false)->after('razorpay_enabled');
            $table->string('custom_label')->nullable()->after('api_secret');
            $table->text('custom_instructions')->nullable()->after('custom_label');
        });
    }

    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->dropColumn([
                'cod_enabled',
                'whatsapp_enabled',
                'razorpay_enabled',
                'custom_enabled',
                'custom_label',
                'custom_instructions',
            ]);
        });
    }
};
