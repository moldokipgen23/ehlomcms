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
    private function requireModule(string $key): void
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule($key), 404);
    }

    public function edit(): View
    {
        $this->requireModule('payments');
        $tenant = app(TenantContext::class)->get();
        $paymentSetting = PaymentSetting::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'provider' => 'razorpay',
                'cod_enabled' => true,
                'whatsapp_enabled' => true,
                'razorpay_enabled' => false,
                'custom_enabled' => false,
                'api_key' => '',
                'api_secret' => '',
            ],
        );

        return view('tenant.payments.index', compact('tenant', 'paymentSetting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->requireModule('payments');
        $tenant = app(TenantContext::class)->get();

        $paymentSetting = PaymentSetting::firstOrNew(['tenant_id' => $tenant->id]);

        $data = $request->validate([
            'cod_enabled' => ['nullable', 'boolean'],
            'whatsapp_enabled' => ['nullable', 'boolean'],
            'razorpay_enabled' => ['nullable', 'boolean'],
            'custom_enabled' => ['nullable', 'boolean'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'max:255'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'custom_label' => ['nullable', 'string', 'max:80'],
            'custom_instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        $razorpayEnabled = $request->boolean('razorpay_enabled');

        if ($razorpayEnabled && empty($data['api_key']) && empty($paymentSetting->api_key)) {
            return back()->withErrors(['api_key' => 'Razorpay Key ID is required when Razorpay is enabled.'])->withInput();
        }

        if ($razorpayEnabled && empty($data['api_secret']) && empty($paymentSetting->api_secret)) {
            return back()->withErrors(['api_secret' => 'Razorpay Key Secret is required when Razorpay is enabled.'])->withInput();
        }

        $paymentSetting->fill([
            'provider' => 'razorpay',
            'cod_enabled' => $request->boolean('cod_enabled'),
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled'),
            'razorpay_enabled' => $razorpayEnabled,
            'custom_enabled' => $request->boolean('custom_enabled'),
            'api_key' => $data['api_key'] ?: ($paymentSetting->api_key ?? ''),
            'api_secret' => $data['api_secret'] ?: ($paymentSetting->api_secret ?? ''),
            'webhook_secret' => $data['webhook_secret'] ?: ($paymentSetting->webhook_secret ?? ''),
            'custom_label' => $data['custom_label'] ?? null,
            'custom_instructions' => $data['custom_instructions'] ?? null,
        ]);
        $paymentSetting->tenant_id = $tenant->id;
        $paymentSetting->save();

        $tenant->update([
            'action_type' => $razorpayEnabled ? 'razorpay' : ($request->boolean('whatsapp_enabled') ? 'whatsapp' : 'custom'),
        ]);

        return redirect()->route('tenant.payments')->with('success', 'Payment settings saved.');
    }
}
