<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AddonProduct;
use App\Models\Setting;
use App\Models\TenantAddon;
use App\Services\InvoiceAutoGenerator;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Marketplace add-on purchases (paying the AGENCY, not the tenant's own
 * customers) are charged through the agency's own Razorpay account
 * (Setting::platform_razorpay_key_id/secret), never the tenant's own
 * PaymentSetting - that would be circular for any tenant buying the
 * Razorpay Gateway add-on itself, and wrong even for other add-ons since a
 * tenant's keys are for charging THEIR customers, not for paying Ehlom.
 */
class TenantAddonCheckoutController extends Controller
{
    private function platformKeys(): ?array
    {
        $keyId = Setting::get('platform_razorpay_key_id');
        $keySecret = Setting::get('platform_razorpay_key_secret');

        if (!$keyId || !$keySecret) {
            return null;
        }

        return [$keyId, $keySecret];
    }

    public function create(AddonProduct $addon): View|RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $existing = TenantAddon::where('tenant_id', $tenant->id)
            ->where('addon_key', $addon->key)
            ->first();

        if ($existing && in_array($existing->status, ['active', 'pending'], true)) {
            return redirect()->route('tenant.addons')->with('error', 'You already have this add-on requested or active.');
        }

        $keys = $this->platformKeys();

        if (!$keys) {
            return redirect()->route('tenant.addons')->with('error', "Add-on purchases aren't available yet. Please contact support.");
        }

        [$keyId, $keySecret] = $keys;

        $amount = (int) round($addon->price * 1.18 * 100);

        try {
            $rzp = new \Razorpay\Api\Api($keyId, $keySecret);
            $order = $rzp->order->create([
                'amount' => $amount,
                'currency' => 'INR',
                'receipt' => "addon_{$addon->key}_{$tenant->id}_" . time(),
                'payment_capture' => 1,
                'notes' => [
                    'tenant_id' => $tenant->id,
                    'addon_key' => $addon->key,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay order creation failed for add-on purchase', [
                'tenant_id' => $tenant->id,
                'addon_key' => $addon->key,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.addons')->with('error', 'Could not start checkout. Please try again shortly.');
        }

        return view('tenant.addons.payment', [
            'tenant' => $tenant,
            'addon' => $addon,
            'razorpayKeyId' => $keyId,
            'order' => $order,
        ]);
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
            if ($addonKey && $paymentId) {
                $addonRecord = TenantAddon::create([
                    'tenant_id' => $tenant->id,
                    'addon_key' => $addonKey,
                    'status' => 'active',
                    'activated_at' => now(),
                ]);
            } else {
                return view('tenant.addons.success', [
                    'addonRecord' => (object) ['addon_key' => $addonKey, 'status' => 'unknown', 'addonMeta' => null],
                    'invoice' => null,
                ]);
            }
        } elseif ($addonRecord->status !== 'active') {
            $addonRecord->update(['status' => 'active', 'activated_at' => now()]);
        }

        $addonRecord->addonMeta = AddonProduct::where('key', $addonKey)->first();

        $invoice = null;
        if ($addonRecord->addonMeta) {
            try {
                $invoice = app(InvoiceAutoGenerator::class)->forAddon(
                    $tenant,
                    $addonRecord->addonMeta->name ?? $addonKey,
                    (float) ($addonRecord->addonMeta->price ?? 0) * 1.18
                );
            } catch (\Throwable $e) {
                Log::error('Auto invoice failed for add-on', [
                    'tenant_id' => $tenant->id,
                    'addon_key' => $addonKey,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('tenant.addons.success', compact('addonRecord', 'invoice'));
    }
}
