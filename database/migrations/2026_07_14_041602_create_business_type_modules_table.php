<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-editable business-type <-> module assignment. Replaces the
        // hardcoded 'default_modules' array in config/business_types.php,
        // which only a code change could edit. A module still requires code
        // (a controller + views gated by Tenant::hasModule) to exist at all -
        // this table only controls which business types include it for free
        // by default. Paid features use the equivalent existing mechanism:
        // AddonProduct.business_types (see Wave 1.5).
        Schema::create('business_type_modules', function (Blueprint $table) {
            $table->id();
            $table->string('business_type');
            $table->string('module_key');
            $table->timestamps();

            $table->unique(['business_type', 'module_key']);
        });

        // Backfill from the config that has been the source of truth so far,
        // so this migration is a pure refactor - no visible change on deploy.
        $businessTypes = config('business_types', []);
        $now = now();
        $rows = [];

        foreach ($businessTypes as $typeKey => $type) {
            foreach ($type['default_modules'] ?? [] as $moduleKey) {
                $rows[] = [
                    'business_type' => $typeKey,
                    'module_key' => $moduleKey,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows) {
            \Illuminate\Support\Facades\DB::table('business_type_modules')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_type_modules');
    }
};
