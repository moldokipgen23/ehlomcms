@extends('tenant.layouts.dashboard')

@section('title', 'Checkout - {{ $addon->name }}')

@section('content')
@php
    $suffix = ($addon->billing_cycle ?? 'monthly') === 'one_time' ? 'once' : '/' . $addon->billingLabel();
    $cycleLabel = ($addon->billing_cycle ?? 'monthly') === 'one_time' ? 'One-time price' : ucfirst($addon->billing_cycle ?? 'monthly') . ' price';
@endphp
<div class="eos-row" style="max-width:600px;margin:0 auto;">
    <div class="eos-card">
        <div class="eos-card-header">
            <div class="eos-card-title">{{ $addon->name }}</div>
        </div>
        <div class="eos-card-body">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <i class="ti {{ $addon->icon }}" style="font-size:32px;color:var(--accent-teal);"></i>
                <div>
                    <div style="font-size:18px;font-weight:600;">{{ $addon->name }}</div>
                    <div style="font-size:14px;color:var(--text-muted);">₹{{ number_format($addon->price, 0) }} {{ $suffix }}</div>
                </div>
            </div>
            <div style="font-size:14px;color:var(--text-secondary);margin-bottom:20px;line-height:1.6;">{{ $addon->description }}</div>

            <div style="border-top:1px solid var(--border);padding-top:16px;margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;font-size:14px;">
                    <span style="color:var(--text-muted);">{{ $cycleLabel }}</span>
                    <span style="font-weight:600;">₹{{ number_format($addon->price, 0) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;margin-top:8px;">
                    <span style="color:var(--text-muted);">GST (18%)</span>
                    <span style="font-weight:600;">₹{{ number_format($addon->price * 0.18, 0) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:16px;margin-top:12px;font-weight:700;border-top:1px solid var(--border);padding-top:12px;">
                    <span>{{ ($addon->billing_cycle ?? 'monthly') === 'one_time' ? 'Total' : 'Total (first cycle)' }}</span>
                    <span>₹{{ number_format($addon->price * 1.18, 0) }}</span>
                </div>
            </div>

            <form action="{{ route('tenant.addons.checkout', $addon->key) }}" method="POST">
                @csrf
                <button type="submit" class="eos-btn eos-btn-primary" style="width:100%;padding:14px;font-size:16px;font-weight:600;">
                    <i class="ti ti-credit-card"></i> Pay ₹{{ number_format($addon->price * 1.18, 0) }} & Subscribe
                </button>
            </form>

            <p style="font-size:12px;color:var(--text-muted);text-align:center;margin-top:16px;">
                You'll be redirected to Razorpay for secure payment. {{ ($addon->billing_cycle ?? 'monthly') === 'one_time' ? 'This is a one-time add-on.' : 'Renewal is tracked in Services & Billing.' }}
            </p>
        </div>
    </div>
</div>
@endsection
