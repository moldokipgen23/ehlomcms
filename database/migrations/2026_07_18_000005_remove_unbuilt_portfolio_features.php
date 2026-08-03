<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $unbuilt = ['client_portal', 'project_mgmt', 'crm'];

        DB::table('business_type_modules')
            ->where('business_type', 'business')
            ->whereIn('module_key', $unbuilt)
            ->delete();

        DB::table('tenants')->where('site_type', 'business')->orderBy('id')->chunkById(100, function ($tenants) use ($unbuilt) {
            foreach ($tenants as $tenant) {
                $modules = json_decode($tenant->modules ?? '[]', true) ?: [];
                DB::table('tenants')->where('id', $tenant->id)->update([
                    'modules' => json_encode(array_values(array_diff($modules, $unbuilt))),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Unbuilt features are intentionally not restored automatically.
    }
};
