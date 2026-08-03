<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('addon_products')->where('key', 'multi_vendor')->update([
            'active' => false,
            'business_types' => json_encode([]),
            'updated_at' => now(),
        ]);

        DB::table('business_type_modules')
            ->where('business_type', 'shopping')
            ->where('module_key', 'multi_vendor')
            ->delete();

        DB::table('tenants')->orderBy('id')->chunkById(100, function ($tenants) {
            foreach ($tenants as $tenant) {
                $modules = json_decode($tenant->modules ?? '[]', true) ?: [];
                if (!in_array('multi_vendor', $modules, true)) {
                    continue;
                }

                DB::table('tenants')->where('id', $tenant->id)->update([
                    'modules' => json_encode(array_values(array_filter($modules, fn ($module) => $module !== 'multi_vendor'))),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('addon_products')->where('key', 'multi_vendor')->update([
            'active' => true,
            'business_types' => json_encode(['shopping']),
            'updated_at' => now(),
        ]);
    }
};
