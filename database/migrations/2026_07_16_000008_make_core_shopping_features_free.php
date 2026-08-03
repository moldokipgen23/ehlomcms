<?php

use App\Models\BusinessTypeModule;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $freeModules = [
            'product_collections',
            'inventory',
            'search_filters',
            'marketing_sections',
            'wishlist',
        ];

        foreach ($freeModules as $moduleKey) {
            BusinessTypeModule::updateOrCreate(
                ['business_type' => 'shopping', 'module_key' => $moduleKey],
                ['status' => 'free', 'price' => null],
            );
        }

        Tenant::where('site_type', 'shopping')->chunkById(100, function ($tenants) use ($freeModules) {
            foreach ($tenants as $tenant) {
                $tenant->modules = array_values(array_unique(array_merge($tenant->modules ?? [], $freeModules)));
                $tenant->save();
            }
        });
    }

    public function down(): void
    {
        $paidModules = [
            'product_collections' => 799,
            'inventory' => 999,
            'search_filters' => 999,
            'marketing_sections' => 1299,
            'wishlist' => 499,
        ];

        foreach ($paidModules as $moduleKey => $price) {
            BusinessTypeModule::updateOrCreate(
                ['business_type' => 'shopping', 'module_key' => $moduleKey],
                ['status' => 'paid', 'price' => $price],
            );
        }
    }
};
