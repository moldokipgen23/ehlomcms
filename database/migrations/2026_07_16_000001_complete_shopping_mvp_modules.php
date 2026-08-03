<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $freeModules = ['content', 'catalog', 'cart', 'checkout', 'payments', 'orders'];
        $now = now();

        foreach ($freeModules as $moduleKey) {
            DB::table('business_type_modules')->updateOrInsert(
                ['business_type' => 'shopping', 'module_key' => $moduleKey],
                ['status' => 'free', 'price' => null, 'updated_at' => $now, 'created_at' => $now],
            );
        }

        DB::table('business_type_modules')
            ->where('business_type', 'shopping')
            ->whereNotIn('module_key', $freeModules)
            ->delete();

        $shoppingTenants = DB::table('tenants')
            ->where('site_type', 'shopping')
            ->select('id', 'modules')
            ->get();

        foreach ($shoppingTenants as $tenant) {
            $modules = json_decode($tenant->modules ?: '[]', true);
            if (!is_array($modules)) {
                $modules = [];
            }

            DB::table('tenants')
                ->where('id', $tenant->id)
                ->update([
                    'modules' => json_encode(array_values(array_unique(array_merge($modules, $freeModules)))),
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        $remove = ['cart', 'checkout'];

        DB::table('business_type_modules')
            ->where('business_type', 'shopping')
            ->whereIn('module_key', $remove)
            ->delete();

        $shoppingTenants = DB::table('tenants')
            ->where('site_type', 'shopping')
            ->select('id', 'modules')
            ->get();

        foreach ($shoppingTenants as $tenant) {
            $modules = json_decode($tenant->modules ?: '[]', true);
            if (!is_array($modules)) {
                continue;
            }

            DB::table('tenants')
                ->where('id', $tenant->id)
                ->update([
                    'modules' => json_encode(array_values(array_diff($modules, $remove))),
                    'updated_at' => now(),
                ]);
        }
    }
};
