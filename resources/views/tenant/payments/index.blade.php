@extends('tenant.layouts.dashboard')

@section('title', 'Payment Settings')
@section('subtitle', 'Control checkout, COD, WhatsApp orders, and online payments')

@section('content')
<form method="POST" action="{{ route('tenant.payments') }}" class="store-module-shell">
    @csrf

    <section class="store-module-hero">
        <div>
            <div class="store-module-kicker">Checkout Control</div>
            <div class="store-module-title">Payment methods</div>
            <div class="store-module-copy">Choose exactly how customers can place orders. COD and WhatsApp are free checkout modes. Razorpay can be enabled when the gateway add-on is active and keys are configured.</div>
        </div>
        <div class="store-module-stats">
            <div class="store-mini-stat"><strong>{{ $paymentSetting->cod_enabled ? 'On' : 'Off' }}</strong><span>COD</span></div>
            <div class="store-mini-stat"><strong>{{ $paymentSetting->whatsapp_enabled ? 'On' : 'Off' }}</strong><span>WhatsApp</span></div>
            <div class="store-mini-stat"><strong>{{ $paymentSetting->razorpay_enabled ? 'On' : 'Off' }}</strong><span>Razorpay</span></div>
        </div>
    </section>

    <section class="store-panel-clean" style="padding:20px;">
        <div class="store-panel-clean-title" style="font-size:16px;margin-bottom:14px;">Enabled checkout modes</div>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
            <label class="store-action" style="cursor:pointer;">
                <div class="store-action-icon green"><i class="ti ti-cash"></i></div>
                <div>
                    <div class="store-action-name">Cash on Delivery</div>
                    <div class="store-action-text">Allow customers to place unpaid COD orders.</div>
                    <input type="checkbox" name="cod_enabled" value="1" {{ old('cod_enabled', $paymentSetting->cod_enabled) ? 'checked' : '' }} style="margin-top:10px;">
                </div>
            </label>

            <label class="store-action" style="cursor:pointer;">
                <div class="store-action-icon teal"><i class="ti ti-brand-whatsapp"></i></div>
                <div>
                    <div class="store-action-name">WhatsApp Order</div>
                    <div class="store-action-text">Redirect order details to the configured WhatsApp number.</div>
                    <input type="checkbox" name="whatsapp_enabled" value="1" {{ old('whatsapp_enabled', $paymentSetting->whatsapp_enabled) ? 'checked' : '' }} style="margin-top:10px;">
                </div>
            </label>

            <label class="store-action" style="cursor:pointer;">
                <div class="store-action-icon blue"><i class="ti ti-credit-card-pay"></i></div>
                <div>
                    <div class="store-action-name">Razorpay Online Payment</div>
                    <div class="store-action-text">Accept UPI, cards, wallets, and netbanking with the store owner's Razorpay account.</div>
                    <input type="checkbox" name="razorpay_enabled" value="1" {{ old('razorpay_enabled', $paymentSetting->razorpay_enabled) ? 'checked' : '' }} style="margin-top:10px;">
                </div>
            </label>

            <label class="store-action" style="cursor:pointer;">
                <div class="store-action-icon amber"><i class="ti ti-building-bank"></i></div>
                <div>
                    <div class="store-action-name">Custom / Bank Transfer</div>
                    <div class="store-action-text">Show a custom payment instruction for manual payment workflows.</div>
                    <input type="checkbox" name="custom_enabled" value="1" {{ old('custom_enabled', $paymentSetting->custom_enabled) ? 'checked' : '' }} style="margin-top:10px;">
                </div>
            </label>
        </div>
    </section>

    <section class="store-panel-clean" style="padding:20px;">
        <div class="store-panel-clean-title" style="font-size:16px;margin-bottom:14px;">Razorpay API keys</div>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
            <div class="eos-field">
                <label class="eos-label">Key ID</label>
                <input type="text" name="api_key" value="{{ old('api_key') }}" class="eos-input" placeholder="rzp_live_...">
                @if ($paymentSetting->api_key)
                    <div class="eos-row-type" style="margin-top:4px;">Saved key: <code>••••{{ substr($paymentSetting->api_key, -4) }}</code></div>
                @endif
                @error('api_key')<div class="eos-row-type" style="color:var(--accent-red);">{{ $message }}</div>@enderror
            </div>

            <div class="eos-field">
                <label class="eos-label">Key Secret</label>
                <input type="password" name="api_secret" value="{{ old('api_secret') }}" class="eos-input" placeholder="Leave blank to keep saved secret">
                @if ($paymentSetting->api_secret)
                    <div class="eos-row-type" style="margin-top:4px;">Saved secret: <code>••••{{ substr($paymentSetting->api_secret, -4) }}</code></div>
                @endif
                @error('api_secret')<div class="eos-row-type" style="color:var(--accent-red);">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="eos-alert-bar" style="margin-top:14px;margin-bottom:0;">
            <i class="ti ti-shield-lock"></i> Keys are encrypted at rest. Leave fields blank to keep existing keys.
        </div>
    </section>

    <section class="store-panel-clean" style="padding:20px;">
        <div class="store-panel-clean-title" style="font-size:16px;margin-bottom:6px;">Razorpay webhook</div>
        <div class="store-module-copy" style="margin-bottom:14px;">Use a separate webhook secret from Razorpay Dashboard. Add the endpoint below and subscribe to the <code>payment.captured</code> event.</div>
        <div class="eos-field">
            <label class="eos-label">Webhook endpoint</label>
            <input type="text" readonly value="{{ url('/webhook/razorpay/' . $tenant->subdomain) }}" class="eos-input">
        </div>
        <div class="eos-field" style="margin-bottom:0;">
            <label class="eos-label">Webhook secret</label>
            <input type="password" name="webhook_secret" value="{{ old('webhook_secret') }}" class="eos-input" placeholder="Set the same secret in Razorpay Dashboard">
            @if ($paymentSetting->webhook_secret)
                <div class="eos-row-type" style="margin-top:4px;">Saved secret: <code>••••{{ substr($paymentSetting->webhook_secret, -4) }}</code></div>
            @endif
            @error('webhook_secret')<div class="eos-row-type" style="color:var(--accent-red);">{{ $message }}</div>@enderror
        </div>
    </section>

    <section class="store-panel-clean" style="padding:20px;">
        <div class="store-panel-clean-title" style="font-size:16px;margin-bottom:14px;">Custom payment instructions</div>
        <div class="eos-field">
            <label class="eos-label">Payment Label</label>
            <input type="text" name="custom_label" value="{{ old('custom_label', $paymentSetting->custom_label) }}" class="eos-input" placeholder="Bank Transfer / Pay after confirmation">
        </div>
        <div class="eos-field">
            <label class="eos-label">Instructions</label>
            <textarea name="custom_instructions" class="eos-input" rows="4" placeholder="Bank account, UPI ID, payment steps, or customer instructions...">{{ old('custom_instructions', $paymentSetting->custom_instructions) }}</textarea>
        </div>
    </section>

    <div style="text-align:right;">
        <button type="submit" class="eos-btn eos-btn-primary" style="padding:12px 24px;"><i class="ti ti-device-floppy"></i> Save Payment Settings</button>
    </div>
</form>
@endsection
