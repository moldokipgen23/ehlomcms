<?php

use App\Models\BusinessTypeModule;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $modules = config('business_types.school.default_modules', []);
        $now = now();

        foreach ($modules as $module) {
            BusinessTypeModule::updateOrCreate(
                ['business_type' => 'school', 'module_key' => $module],
                ['updated_at' => $now, 'created_at' => $now],
            );
        }

        // Existing School tenants should receive the same website baseline as
        // newly created tenants. Never overwrite a tenant's intentional add-ons.
        Tenant::where('site_type', 'school')->get()->each(function (Tenant $tenant) use ($modules): void {
            $tenant->modules = array_values(array_unique(array_merge($tenant->modules ?? [], $modules)));
            $tenant->save();
        });
    }

    public function down(): void
    {
        $modules = config('business_types.school.default_modules', []);
        DB::table('business_type_modules')
            ->where('business_type', 'school')
            ->whereIn('module_key', $modules)
            ->delete();
    }
};
