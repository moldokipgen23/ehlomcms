<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $staleKeys = [
            'wishlist',
            'filters',
            'reviews',
            'coupons',
            'subscription',
            'multi_vendor',
            'pos',
            'search_filters',
            'variants',
            'inventory',
            'shipping_rules',
            'gst_invoice',
        ];

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
                    'modules' => json_encode(array_values(array_diff($modules, $staleKeys))),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Intentionally no-op: removed keys represented non-working or future
        // Shopping features and should not be restored automatically.
    }
};
