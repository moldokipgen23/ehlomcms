<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // HostingPlan (the new admin-managed price catalog) previously had
        // zero relationship to anything - not a single row could be
        // assigned to a tenant. Tenant already has its own free-text
        // 'plan' string column (unrelated, predates this table); that stays
        // as-is for backward compatibility, this is the real structured
        // link on top of it.
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('hosting_plan_id')->nullable()->after('plan')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['hosting_plan_id']);
            $table->dropColumn('hosting_plan_id');
        });
    }
};
