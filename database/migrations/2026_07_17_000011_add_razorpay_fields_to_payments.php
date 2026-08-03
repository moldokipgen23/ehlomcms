<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status')->default('paid')->after('method');
            $table->string('razorpay_order_id')->nullable()->unique()->after('reference');
            $table->string('razorpay_payment_id')->nullable()->unique()->after('razorpay_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['status', 'razorpay_order_id', 'razorpay_payment_id']);
        });
    }
};
