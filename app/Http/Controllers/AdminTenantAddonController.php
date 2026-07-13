<?php

namespace App\Http\Controllers;

use App\Models\AddonProduct;
use App\Models\Invoice;
use App\Models\TenantAddon;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminTenantAddonController extends Controller
{
    public function index(): View
    {
        $requests = TenantAddon::with('tenant.client')
            ->orderByRaw("FIELD(status, 'pending', 'active', 'inactive')")
            ->orderByDesc('created_at')
            ->get();

        $addons = AddonProduct::get()->keyBy('key');

        return view('tenant-addon-requests.index', compact('requests', 'addons'));
    }

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
        $addon->update(['status' => 'active', 'activated_at' => now()]);

        $tenant = $addon->tenant;
        $addonMeta = AddonProduct::where('key', $addon->addon_key)->first();

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
}
