<?php

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->json('modules')->nullable()->after('template_id');
        });

        // Backfill existing tenants based on current site_type/action_type.
        Tenant::each(function (Tenant $tenant) {
            $modules = [];

            // Every tenant gets content module (About/Gallery/Contact).
            $modules[] = 'content';

            if ($tenant->site_type === 'shopping') {
                $modules[] = 'catalog';
                if ($tenant->action_type === 'razorpay') {
                    $modules[] = 'payments';
                    $modules[] = 'orders';
                }
            }

            $tenant->modules = $modules;
            $tenant->save();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('modules');
        });
    }
};
