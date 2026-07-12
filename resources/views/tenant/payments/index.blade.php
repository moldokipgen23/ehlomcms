@extends('tenant.layouts.dashboard')

@section('title', 'Payment Settings')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Razorpay API Keys</div>
        </div>

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
    </div>
</div>
@endsection
