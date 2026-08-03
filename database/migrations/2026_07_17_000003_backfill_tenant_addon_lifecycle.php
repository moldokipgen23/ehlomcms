<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $addons = DB::table('tenant_addons')
            ->join('addon_products', 'tenant_addons.addon_key', '=', 'addon_products.key')
            ->select(
                'tenant_addons.id',
                'tenant_addons.status',
                'tenant_addons.activated_at',
                'addon_products.price',
                'addon_products.billing_cycle'
            )
            ->whereNull('tenant_addons.billing_cycle')
            ->get();

        foreach ($addons as $addon) {
            $cycle = $addon->billing_cycle ?: 'monthly';
            $activatedAt = $addon->activated_at ? Carbon::parse($addon->activated_at) : now();

            DB::table('tenant_addons')->where('id', $addon->id)->update([
                'billing_cycle' => $cycle,
                'renewal_amount' => $addon->price,
                'expires_at' => $addon->status === 'active' && $cycle !== 'one_time'
                    ? $this->expiryFromCycle($activatedAt, $cycle)
                    : null,
                'auto_invoice' => $cycle !== 'one_time',
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('tenant_addons')->update([
            'billing_cycle' => null,
            'renewal_amount' => null,
            'expires_at' => null,
            'auto_invoice' => true,
            'updated_at' => now(),
        ]);
    }

    private function expiryFromCycle(Carbon $start, string $cycle): Carbon
    {
        return match ($cycle) {
            'quarterly' => $start->copy()->addMonths(3),
            'yearly' => $start->copy()->addYear(),
            default => $start->copy()->addMonth(),
        };
    }
};
