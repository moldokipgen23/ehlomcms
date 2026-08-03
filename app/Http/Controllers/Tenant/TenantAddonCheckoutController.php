<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AddonProduct;
use App\Models\TenantAddon;
use App\Models\Invoice;
use App\Services\InvoiceAutoGenerator;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class TenantAddonCheckoutController extends Controller
{
    public function create(AddonProduct $addon): RedirectResponse
    {
        abort_if($addon->module_key, 404);

        $tenant = app(TenantContext::class)->get();

        $existing = TenantAddon::with('invoice')->where('tenant_id', $tenant->id)
            ->where('addon_key', $addon->key)
            ->first();

        if ($existing?->status === 'active') {
            return back()->with('error', 'This add-on is already active.');
        }
        if ($existing?->invoice && $existing->invoice->status !== 'paid') {
            return redirect()->away($this->paymentLink($existing->invoice));
        }

        $invoice = app(InvoiceAutoGenerator::class)->forAddon($tenant, $addon->name, (float) $addon->price);
        if (! $invoice) {
            return back()->with('error', 'We could not create an Ehlom billing invoice for this add-on. Please contact support.');
        }
        TenantAddon::updateOrCreate(
            ['tenant_id' => $tenant->id, 'addon_key' => $addon->key],
            ['invoice_id' => $invoice->id, 'status' => 'pending', 'billing_cycle' => $addon->billing_cycle ?? 'monthly']
        );

        return redirect()->away($this->paymentLink($invoice));
    }

    public function checkout(Request $request, AddonProduct $addon): RedirectResponse
    {
        abort_if($addon->module_key, 404);

        $tenant = app(TenantContext::class)->get();

        $existing = TenantAddon::where('tenant_id', $tenant->id)
            ->where('addon_key', $addon->key)
            ->first();

        if ($existing && in_array($existing->status, ['active', 'pending'], true)) {
            return back()->with('error', 'You already have this add-on requested or active.');
        }

        return $this->create($addon);
    }

    public function success(Request $request): View
    {
        $tenant = app(TenantContext::class)->get();

        $addonKey = $request->query('addon_key');
        $paymentId = $request->query('payment_id');

        $addonRecord = TenantAddon::where('tenant_id', $tenant->id)
            ->where('addon_key', $addonKey)
            ->first();

        if (!$addonRecord) {
            return view('tenant.addons.success', [
                'addonRecord' => (object) ['addon_key' => $addonKey, 'status' => 'unknown', 'addonMeta' => null],
                'invoice' => null,
            ]);
        }

        $addonRecord->addonMeta = AddonProduct::where('key', $addonKey)->first();

        $invoice = $addonRecord->invoice_id ? Invoice::find($addonRecord->invoice_id) : null;

        return view('tenant.addons.success', compact('addonRecord', 'invoice'));
    }

    private function paymentLink(Invoice $invoice): string
    {
        return URL::temporarySignedRoute('billing.invoices.pay', now()->addDays(30), [
            'portalHost' => parse_url(config('app.url'), PHP_URL_HOST) ?: 'portal.ehlom.com',
            'invoice' => $invoice,
        ]);
    }
}
