<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AddonProduct;
use App\Models\PaymentSetting;
use App\Models\TenantAddon;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantAddonCheckoutController extends Controller
{
    public function create(AddonProduct $addon): View
    {
        $tenant = app(TenantContext::class)->get();

        $existing = TenantAddon::where('tenant_id', $tenant->id)
            ->where('addon_key', $addon->key)
            ->first();

        if ($existing && in_array($existing->status, ['active', 'pending'], true)) {
            return back()->with('error', 'You already have this add-on requested or active.');
        }

        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();

        if (!$paymentSetting || !$paymentSetting->razorpay_key_id || !$paymentSetting->razorpay_key_secret) {
            return back()->with('error', 'Payment not configured. Please contact support.');
        }

        $amount = (int) ($addon->price * 1.18 * 100);

        $rzp = new \Razorpay\Api\Api($paymentSetting->razorpay_key_id, $paymentSetting->razorpay_key_secret);
        $order = $rzp->order->create([
            'amount' => $amount,
            'currency' => 'INR',
            'receipt' => "addon_{$addon->key}_{$tenant->id}_" . time(),
            'payment_capture' => 1,
        ]);

        return view('tenant.addons.payment', compact('tenant', 'addon', 'paymentSetting', 'order'));
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
                $addonProduct = AddonProduct::where('key', $addonKey)->first();
                $addonRecord = TenantAddon::create([
                    'tenant_id' => $tenant->id,
                    'addon_key' => $addonKey,
                    'status' => 'active',
                    'activated_at' => now(),
                ]);
            } else {
                return view('tenant.addons.success', [
                    'addon' => (object) ['addon_key' => $addonKey, 'status' => 'unknown'],
                ]);
            }
        } elseif ($addonRecord->status !== 'active') {
            $addonRecord->update(['status' => 'active', 'activated_at' => now()]);
        }

        $addonRecord->addonMeta = AddonProduct::where('key', $addonKey)->first();

        return view('tenant.addons.success', compact('addonRecord'));
    }
}