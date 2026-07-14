@extends('layouts.app')

@section('title', 'Onboarding Complete')
@section('subtitle', 'Step 5 of 5 — ' . $tenant->name . ' is ready!')

@section('content')
<div style="max-width:600px;">
    @include('onboarding._progress', ['current' => 5])

    <div class="eos-card" style="margin-bottom:16px;border-color:var(--accent-teal);">
        <div class="eos-card-body" style="padding:24px;text-align:center;">
            <div style="width:60px;height:60px;border-radius:50%;background:var(--accent-teal-alpha,#d1fae5);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="ti ti-check" style="font-size:28px;color:var(--accent-teal);"></i>
            </div>
            <div style="font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:8px;">Onboarding Complete!</div>
            <div style="font-size:13px;color:var(--text-secondary);margin-bottom:20px;">{{ $tenant->name }} is configured and ready.</div>

            @if (session('generated_login'))
                @php $gl = session('generated_login'); @endphp
                <div style="background:var(--bg-hover);border-radius:8px;padding:16px;text-align:left;margin-bottom:20px;">
                    <div style="font-weight:600;color:var(--accent-teal);margin-bottom:8px;"><i class="ti ti-key"></i> Owner Login Credentials</div>
                    <div style="font-size:12px;color:var(--text-secondary);line-height:1.8;">
                        <strong>Site:</strong> {{ $gl['subdomain'] }}.{{ config('app.tenant_domain', 'ehlom.com') }}/dashboard/login<br>
                        <strong>Email:</strong> {{ $gl['email'] }}<br>
                        <strong>Password:</strong> {{ $gl['password'] }}
                    </div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:8px;">Save these credentials — the password won't be shown again.</div>
                </div>
            @endif

            <div style="display:flex;gap:10px;justify-content:center;">
                <a href="{{ request()->getScheme() }}://{{ $tenant->subdomain }}.{{ config('app.tenant_domain', 'ehlom.com') }}" target="_blank" class="eos-btn eos-btn-primary" style="padding:10px 20px;"><i class="ti ti-external-link"></i> Visit Site</a>
                <a href="{{ route('tenants.edit', $tenant) }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;"><i class="ti ti-settings"></i> Manage</a>
                <a href="{{ route('tenants.index') }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;">Dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
