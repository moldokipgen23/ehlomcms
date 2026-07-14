@extends('layouts.app')

@section('title', 'Onboarding: Domain Setup')
@section('subtitle', 'Step 4 of 5 — Configure domain for ' . $tenant->name)

@section('content')
<div style="max-width:700px;">
    @include('onboarding._progress', ['current' => 4])

    <div class="eos-card" style="margin-bottom:16px;">
        <div class="eos-card-header">
            <div class="eos-card-title"><i class="ti ti-world"></i> Subdomain (Active)</div>
        </div>
        <div class="eos-card-body" style="padding:16px;">
            <div style="font-size:13px;color:var(--text-primary);margin-bottom:8px;">
                Your site is live at: <a href="{{ request()->getScheme() }}://{{ $tenant->subdomain }}.{{ config('app.tenant_domain', 'ehlom.com') }}" target="_blank" style="color:var(--accent-teal);font-weight:600;">{{ $tenant->subdomain }}.{{ config('app.tenant_domain', 'ehlom.com') }}</a>
            </div>
            <div style="font-size:11px;color:var(--text-dim);">No additional setup needed for the subdomain.</div>
        </div>
    </div>

    <div class="eos-card" style="margin-bottom:16px;">
        <div class="eos-card-header">
            <div class="eos-card-title"><i class="ti ti-link"></i> Custom Domain (Optional)</div>
        </div>
        <div class="eos-card-body" style="padding:16px;">
            <div style="font-size:12px;color:var(--text-secondary);margin-bottom:12px;line-height:1.6;">
                If the client has their own domain (e.g. <code>shop.client.com</code>), enter it below. After setting, you'll need to configure DNS at the domain registrar.
            </div>

            <form method="POST" action="{{ route('onboarding.update', ['tenant' => $tenant, 'step' => 'domain']) }}">
                @csrf
                <div class="eos-field">
                    <label class="eos-label">Custom Domain</label>
                    <input type="text" name="custom_domain" value="{{ old('custom_domain', $tenant->custom_domain ?? '') }}" class="eos-input" placeholder="shop.example.com">
                </div>

                @if ($tenant->custom_domain && $tenant->domain_status === 'pending')
                    <div style="background:var(--bg-hover);border-radius:8px;padding:12px;margin-top:12px;">
                        <div style="font-weight:600;font-size:12px;color:var(--text-primary);margin-bottom:8px;"><i class="ti ti-alert-circle"></i> DNS Configuration Required</div>
                        <ol style="font-size:11px;color:var(--text-secondary);line-height:1.8;padding-left:16px;margin:0;">
                            <li>Log in to your domain registrar (GoDaddy, Namecheap, etc.)</li>
                            <li>Go to DNS Management for <strong>{{ $tenant->custom_domain }}</strong></li>
                            <li>Add a <strong>CNAME Record</strong>:
                                <ul style="margin:2px 0;padding-left:16px;">
                                    <li><strong>Host/Name:</strong> <code>@</code></li>
                                    <li><strong>Value/Target:</strong> <code>{{ config('app.tenant_domain', 'ehlom.com') }}</code></li>
                                    <li><strong>TTL:</strong> <code>300</code></li>
                                </ul>
                            </li>
                            <li>Wait 5-30 minutes, then come back and click <strong>Verify</strong> on the <a href="{{ route('domains.admin.index') }}" style="color:var(--accent-teal);">Custom Domains</a> page</li>
                        </ol>
                    </div>
                @endif

                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
                    <a href="{{ route('onboarding.step', ['tenant' => $tenant, 'step' => 'modules']) }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;"><i class="ti ti-arrow-left"></i> Back</a>
                    <a href="{{ route('onboarding.skip', $tenant) }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;">Skip</a>
                    <button type="submit" class="eos-btn eos-btn-primary" style="padding:10px 20px;">Continue <i class="ti ti-arrow-right"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
