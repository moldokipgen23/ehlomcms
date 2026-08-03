<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addon_products', function (Blueprint $table) {
            $table->string('billing_cycle')->default('monthly')->after('price');
        });

        Schema::table('tenant_addons', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('activated_at');
            $table->decimal('renewal_amount', 10, 2)->nullable()->after('expires_at');
            $table->string('billing_cycle')->nullable()->after('renewal_amount');
            $table->boolean('auto_invoice')->default(true)->after('billing_cycle');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_addons', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'renewal_amount', 'billing_cycle', 'auto_invoice']);
        });

        Schema::table('addon_products', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });
    }
};
