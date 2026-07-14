@extends('tenant.layouts.dashboard')

@section('title', 'Subscription Activated')

@section('content')
<div class="eos-row" style="max-width:500px;margin:0 auto;">
    <div class="eos-card" style="text-align:center;padding:40px 24px;">
        <div style="width:80px;height:80px;border-radius:50%;background:var(--accent-teal-alpha,#d1fae5);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <i class="ti ti-circle-check" style="font-size:36px;color:var(--accent-teal);"></i>
        </div>
        <h2 style="font-size:22px;font-weight:700;margin-bottom:8px;color:var(--text-primary);">Subscription Activated!</h2>
        <p style="font-size:14px;color:var(--text-secondary);margin-bottom:24px;">
            {{ $addonRecord->addonMeta->name ?? $addonRecord->addon_key }} has been activated.
        </p>

        <div style="background:var(--bg-hover);border-radius:12px;padding:20px;margin-bottom:24px;text-align:left;">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px;">Add-on</div>
            <div style="font-weight:600;">{{ $addonRecord->addonMeta->name ?? $addonRecord->addon_key }}</div>
            <div style="font-size:13px;color:var(--text-muted);margin-top:12px;margin-bottom:4px;">Status</div>
            <div style="font-weight:600;color:var(--accent-teal);">{{ ucfirst($addonRecord->status) }}</div>
            @if ($addonRecord->activated_at)
                <div style="font-size:13px;color:var(--text-muted);margin-top:12px;margin-bottom:4px;">Activated</div>
                <div>{{ $addonRecord->activated_at->format('M j, Y H:i') }}</div>
            @endif
        </div>

        <a href="{{ route('tenant.addons') }}" class="eos-btn eos-btn-primary" style="width:100%;padding:14px;font-size:16px;">
            <i class="ti ti-shopping-bag"></i> Back to Marketplace
        </a>
    </div>
</div>
@endsection