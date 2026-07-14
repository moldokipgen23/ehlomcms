<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addon_products', function (Blueprint $table) {
            // Set only for add-ons auto-managed from the Business Modules
            // page marking a real dashboard module "Paid" for a business
            // type (see AdminModuleController::syncModuleAddonProducts).
            // Null for genuine standalone products (AI Agent, Analytics
            // Pro, ...) that aren't gated dashboard modules at all.
            $table->string('module_key')->nullable()->after('key');
        });
    }

    public function down(): void
    {
        Schema::table('addon_products', function (Blueprint $table) {
            $table->dropColumn('module_key');
        });
    }
};
