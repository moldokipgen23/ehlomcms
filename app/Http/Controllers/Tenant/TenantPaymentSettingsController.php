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
    public function edit(): View
    {
        $tenant = app(TenantContext::class)->get();
        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();

        return view('tenant.payments.index', compact('tenant', 'paymentSetting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

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
