@extends('tenant.layouts.dashboard')

@section('title', 'Order Confirmed')

@section('content')
<div class="eos-row" style="max-width:500px;margin:0 auto;">
    <div class="eos-card" style="text-align:center;padding:40px 24px;">
        <div style="width:80px;height:80px;border-radius:50%;background:var(--accent-teal-alpha,#d1fae5);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <i class="ti ti-circle-check" style="font-size:36px;color:var(--accent-teal);"></i>
        </div>
        <h2 style="font-size:22px;font-weight:700;margin-bottom:8px;color:var(--text-primary);">Order Confirmed!</h2>
        <p style="font-size:14px;color:var(--text-secondary);margin-bottom:24px;">
            Your {{ $type ?? 'product' }} order has been placed successfully.
        </p>

        <div style="background:var(--bg-hover);border-radius:12px;padding:20px;margin-bottom:24px;text-align:left;">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px;">{{ $type === 'domain' ? 'Domain' : 'Hosting' }}</div>
            <div style="font-weight:600;">{{ $product->name ?? 'Product' }}</div>
            <div style="font-size:13px;color:var(--text-muted);margin-top:12px;margin-bottom:4px;">Billing Cycle</div>
            <div style="font-weight:600;">{{ ucfirst($product->billing_cycle ?? 'monthly') }}</div>
            <div style="font-size:13px;color:var(--text-muted);margin-top:12px;margin-bottom:4px;">Status</div>
            <div style="font-weight:600;color:var(--accent-teal);">Payment Confirmed</div>
        </div>

        <a href="{{ route('tenant.infrastructure') }}" class="eos-btn eos-btn-primary" style="width:100%;padding:14px;font-size:16px;">
            <i class="ti ti-server"></i> Back to Domains & Hosting
        </a>
    </div>
</div>
@endsection