@extends('tenant.layouts.dashboard')

@section('title', 'Payment Settings')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Razorpay API Keys</div>
        </div>

        @if ($locked)
            <div style="padding:20px;">
                <div class="eos-alert-bar" style="margin-bottom:16px;">
                    <i class="ti ti-lock"></i> Your store currently accepts <strong>Cash on Delivery</strong> only. Buy the Razorpay Payment Gateway add-on (one-time) to accept card, UPI, and netbanking payments with your own Razorpay account.
                </div>
                <a href="{{ route('tenant.addons') }}" class="eos-btn eos-btn-primary" style="display:inline-block;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;background:var(--accent-teal);color:#fff;">
                    <i class="ti ti-shopping-cart"></i> Go to Marketplace
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('tenant.payments') }}" style="padding:16px;">
                @csrf

                <div class="eos-field">
                    <label class="eos-label">Key ID</label>
                    <input type="text" name="api_key" value="{{ old('api_key', '') }}" class="eos-input" placeholder="rzp_live_..." required>
                    @if ($paymentSetting)
                        <div class="eos-row-type" style="margin-top:4px;">Saved key: <code>••••{{ substr($paymentSetting->api_key, -4) }}</code></div>
                    @endif
                </div>

                <div class="eos-field">
                    <label class="eos-label">Key Secret</label>
                    <input type="password" name="api_secret" value="{{ old('api_secret', '') }}" class="eos-input" placeholder="Enter your secret key" required>
                    @if ($paymentSetting)
                        <div class="eos-row-type" style="margin-top:4px;">Saved: <code>••••{{ substr($paymentSetting->api_secret, -4) }}</code></div>
                    @endif
                </div>

                <div class="eos-alert-bar" style="margin-bottom:14px;">
                    <i class="ti ti-shield-lock"></i> Your keys are encrypted at rest. Ehlom never has access to your API secret.
                </div>

                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save Keys</button>
            </form>
        @endif
    </div>
</div>
@endsection
