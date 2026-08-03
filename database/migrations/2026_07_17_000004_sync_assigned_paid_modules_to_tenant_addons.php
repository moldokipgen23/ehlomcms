<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $addons = DB::table('addon_products')
            ->where('active', true)
            ->whereNotNull('module_key')
            ->get();

        $moduleConfig = config('modules');
        $now = now();

        DB::table('tenants')
            ->select('id', 'site_type', 'modules')
            ->orderBy('id')
            ->chunkById(100, function ($tenants) use ($addons, $moduleConfig, $now) {
                foreach ($tenants as $tenant) {
                    $modules = json_decode($tenant->modules ?? '[]', true) ?: [];

                    foreach ($addons as $addon) {
                        $businessTypes = json_decode($addon->business_types ?? '[]', true) ?: [];
                        if ($businessTypes && !in_array($tenant->site_type, $businessTypes, true)) {
                            continue;
                        }

                        if (!in_array($addon->module_key, $modules, true)) {
                            continue;
                        }

                        if (!empty($moduleConfig[$addon->module_key]['free'])) {
                            continue;
                        }

                        $cycle = $addon->billing_cycle ?: 'monthly';

                        DB::table('tenant_addons')->updateOrInsert(
                            ['tenant_id' => $tenant->id, 'addon_key' => $addon->key],
                            [
                                'status' => 'active',
                                'activated_at' => $now,
                                'expires_at' => $this->expiryFromCycle($now, $cycle),
                                'renewal_amount' => $addon->price,
                                'billing_cycle' => $cycle,
                                'auto_invoice' => true,
                                'updated_at' => $now,
                                'created_at' => $now,
                            ],
                        );
                    }
                }
            }, 'id');
    }

    public function down(): void
    {
        // Keep tenant add-on records. This migration only backfills live
        // billing state from already-assigned modules.
    }

    private function expiryFromCycle(Carbon $start, string $cycle): ?Carbon
    {
        return match ($cycle) {
            'one_time' => null,
            'quarterly' => $start->copy()->addQuarter(),
            'yearly' => $start->copy()->addYear(),
            default => $start->copy()->addMonth(),
        };
    }
};
