<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('custom_domain')->nullable()->after('subdomain');
            $table->string('domain_status')->default('none')->after('custom_domain'); // none, pending, verified
            $table->timestamp('domain_verified_at')->nullable()->after('domain_status');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['custom_domain', 'domain_status', 'domain_verified_at']);
        });
    }
};
