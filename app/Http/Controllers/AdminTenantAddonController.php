<?php

namespace App\Http\Controllers;

use App\Models\AddonProduct;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Services\InvoiceService;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminTenantAddonController extends Controller
{
    /**
     * Activation is the payment-confirmation gate itself: the agency has
     * collected payment however it currently does so (offline - see
     * docs/SAAS_REQUIREMENTS_AND_GAPS.md), and this button is what actually
     * unlocks the add-on for the tenant. If the tenant is linked to an
     * existing CRM Client, the charge is also recorded as a real line item
     * on that client's invoices, using the same Invoice/InvoiceItem models
     * that already power the agency's renewal-invoice automation - not a
     * second, disconnected ledger.
     */
    public function activate(TenantAddon $addon, InvoiceService $invoiceService): RedirectResponse
    {
        $tenant = $addon->tenant;
        $addonMeta = AddonProduct::where('key', $addon->addon_key)->first();
        $this->activateRecord($addon, $addonMeta);

        if ($tenant?->client_id && $addonMeta) {
            $price = (float) $addonMeta['price'];

            $invoice = Invoice::create([
                'invoice_number' => $invoiceService->nextInvoiceNumber(),
                'client_id' => $tenant->client_id,
                'subtotal' => $price,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'tax' => 0,
                'total' => $price,
                'due_date' => now()->addDays(7),
                'status' => 'draft',
                'notes' => 'Add-on activation — ' . $addonMeta['name'] . ' for ' . $tenant->name . '.',
            ]);

            $invoice->items()->create([
                'description' => $addonMeta['name'] . ' — ' . $tenant->name,
                'quantity' => 1,
                'unit_price' => $price,
                'total' => $price,
            ]);
        }

        return back()->with('success', ($addonMeta['name'] ?? $addon->addon_key) . ' activated for ' . ($tenant->name ?? 'tenant') . '.');
    }

    public function deactivate(TenantAddon $addon): RedirectResponse
    {
        $addon->update(['status' => 'inactive']);

        return back()->with('success', 'Add-on deactivated.');
    }

    /**
     * Manual grant: agency gives an add-on to a tenant for free (no payment,
     * no invoice). Useful for courtesy, demos, or agency-managed clients.
     */
    public function grant(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'addon_key' => 'required|string|exists:addon_products,key',
        ]);

        $addonMeta = AddonProduct::where('key', $request->addon_key)->where('active', true)->firstOrFail();

        $addon = TenantAddon::updateOrCreate(
            ['tenant_id' => $tenant->id, 'addon_key' => $request->addon_key],
            ['status' => 'active', 'activated_at' => now()],
        );
        $this->activateRecord($addon, $addonMeta);

        return back()->with('success', $addonMeta->name . ' granted to ' . $tenant->name . ' (manual, no charge).');
    }

    private function activateRecord(TenantAddon $addon, ?AddonProduct $addonMeta): void
    {
        $activatedAt = now();
        $cycle = $addonMeta?->billing_cycle ?? 'monthly';

        $addon->update([
            'status' => 'active',
            'activated_at' => $activatedAt,
            'expires_at' => $cycle === 'one_time' ? null : $this->expiryFromCycle($activatedAt, $cycle),
            'renewal_amount' => $addonMeta ? (float) $addonMeta->price : null,
            'billing_cycle' => $cycle,
            'auto_invoice' => $cycle !== 'one_time',
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
}
