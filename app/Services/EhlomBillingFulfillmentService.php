<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantAddon;
use Illuminate\Support\Carbon;

class EhlomBillingFulfillmentService
{
    /** Activate an Ehlom marketplace add-on only after its invoice is paid. */
    public function fulfillInvoice(Invoice $invoice): void
    {
        if ($invoice->status !== 'paid') {
            return;
        }

        $this->fulfillInfrastructurePurchase($invoice);

        $addon = TenantAddon::with('addonMeta')->where('invoice_id', $invoice->id)->first();
        if (! $addon || $addon->status === 'active') {
            return;
        }

        $activatedAt = now();
        $cycle = $addon->addonMeta?->billing_cycle ?? 'monthly';
        $addon->update([
            'status' => 'active',
            'activated_at' => $activatedAt,
            'expires_at' => $cycle === 'one_time' ? null : $this->expiryFromCycle($activatedAt, $cycle),
            'renewal_amount' => $addon->addonMeta ? (float) $addon->addonMeta->price : null,
            'billing_cycle' => $cycle,
            'auto_invoice' => $cycle !== 'one_time',
        ]);
    }

    /**
     * Assign hosting/domain products only after the Ehlom invoice is paid.
     * The reference is written into the invoice by the tenant marketplace
     * checkout and makes the operation idempotent across webhook/retry calls.
     */
    private function fulfillInfrastructurePurchase(Invoice $invoice): void
    {
        if (! preg_match('/tenant:(\d+);product:(\d+)/', (string) $invoice->notes, $matches)) {
            return;
        }

        $tenant = Tenant::with('client')->find((int) $matches[1]);
        $product = Product::find((int) $matches[2]);

        if (! $tenant?->client || ! $product || ! in_array($product->category, ['hosting', 'domain'], true)) {
            return;
        }

        if ($product->category === 'hosting') {
            $tenant->update(['hosting_plan_id' => $product->id]);
        }

        app(ClientServiceLedger::class)->assignProduct($tenant->client, $product);
    }

    private function expiryFromCycle(Carbon $start, string $cycle): Carbon
    {
        return match ($cycle) {
            'quarterly' => $start->copy()->addMonths(3),
            'yearly' => $start->copy()->addYear(),
            default => $start->copy()->addMonth(),
        };
    }
}
