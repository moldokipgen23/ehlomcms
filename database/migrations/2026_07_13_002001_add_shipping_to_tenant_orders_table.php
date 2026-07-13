<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_orders', function (Blueprint $table) {
            $table->string('shipping_name')->nullable()->after('customer_details');
            $table->string('shipping_phone')->nullable()->after('shipping_name');
            $table->text('shipping_address')->nullable()->after('shipping_phone');
            $table->string('shipping_pincode', 10)->nullable()->after('shipping_address');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_name', 'shipping_phone', 'shipping_address', 'shipping_pincode']);
        });
    }
};
