<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantPaymentSettingsController extends Controller
{
    /**
     * Razorpay is a one-time paid upgrade for Shopping tenants only (COD is
     * free/default). Other verticals (restaurant, business, school) keep
     * unrestricted access to Payment Settings - this gate is intentionally
     * scoped to 'shopping' per product decision 2026-07-16, not applied
     * platform-wide.
     */
    private function razorpayLocked($tenant): bool
    {
        return $tenant->site_type === 'shopping' && !$tenant->hasActiveAddon('razorpay_gateway');
    }

    private function requireModule(string $key): void
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule($key), 404);
    }

    public function edit(): View
    {
        $this->requireModule('payments');
        $tenant = app(TenantContext::class)->get();
        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();
        $locked = $this->razorpayLocked($tenant);

        return view('tenant.payments.index', compact('tenant', 'paymentSetting', 'locked'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->requireModule('payments');
        $tenant = app(TenantContext::class)->get();

        if ($this->razorpayLocked($tenant)) {
            return redirect()->route('tenant.payments')->with('error', 'Buy the Razorpay Payment Gateway add-on from the Marketplace to accept online payments.');
        }

        $data = $request->validate([
            'api_key' => ['required', 'string', 'max:255'],
            'api_secret' => ['required', 'string', 'max:255'],
        ]);

        PaymentSetting::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'provider' => 'razorpay',
                'api_key' => $data['api_key'],
                'api_secret' => $data['api_secret'],
            ],
        );

        return redirect()->route('tenant.payments')->with('success', 'Payment settings saved. Your API keys are encrypted at rest.');
    }
}
