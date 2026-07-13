<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            // Raw HTML pasted by a non-technical admin, using simple
            // {{tenant.name}}-style placeholder tokens - never compiled as
            // Blade/PHP, only ever string-substituted (see
            // App\Services\CustomThemeRenderer). When set, this takes
            // priority over base_template entirely.
            $table->longText('custom_html')->nullable()->after('base_template');
        });

        // base_template was required because every theme used to point at a
        // real Blade folder. A custom-HTML theme doesn't need one.
        DB::statement('ALTER TABLE themes MODIFY base_template VARCHAR(255) NULL');
    }

    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn('custom_html');
        });

        DB::statement('ALTER TABLE themes MODIFY base_template VARCHAR(255) NOT NULL');
    }
};
