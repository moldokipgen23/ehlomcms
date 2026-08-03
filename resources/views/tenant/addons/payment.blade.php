@extends('tenant.layouts.dashboard')

@section('title', 'Secure Payment - {{ $addon->name }}')

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endpush

@section('content')
@php
    $suffix = ($addon->billing_cycle ?? 'monthly') === 'one_time' ? 'once' : '/' . $addon->billingLabel();
    $cycleLabel = ($addon->billing_cycle ?? 'monthly') === 'one_time' ? 'One-time price' : ucfirst($addon->billing_cycle ?? 'monthly') . ' price';
@endphp
<div class="eos-row" style="max-width:500px;margin:0 auto;">
    <div class="eos-card">
        <div class="eos-card-header">
            <div class="eos-card-title">Secure Checkout</div>
        </div>
        <div class="eos-card-body">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <i class="ti {{ $addon->icon }}" style="font-size:32px;color:var(--accent-teal);"></i>
                <div>
                    <div style="font-size:16px;font-weight:600;">{{ $addon->name }}</div>
                    <div style="font-size:13px;color:var(--text-muted);">₹{{ number_format($addon->price, 0) }} {{ $suffix }} + GST</div>
                </div>
            </div>

            <div style="background:var(--bg-hover);border-radius:8px;padding:16px;margin-bottom:20px;">
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

            <button id="rzp-btn" class="eos-btn eos-btn-primary" style="width:100%;padding:16px;font-size:16px;font-weight:600;" disabled>
                <i class="ti ti-lock"></i> Initializing...
            </button>

            <p style="font-size:12px;color:var(--text-muted);text-align:center;margin-top:16px;">
                Secured by Razorpay. {{ ($addon->billing_cycle ?? 'monthly') === 'one_time' ? 'This is a one-time add-on.' : 'Renewal is tracked in Services & Billing. Cancel anytime from Marketplace.' }}
            </p>
        </div>
    </div>
</div>

<script>
var options = {
    key: "{{ $paymentSetting->razorpay_key_id }}",
    amount: {{ (int) ($addon->price * 1.18 * 100) }},
    currency: "INR",
    name: "{{ $tenant->name }}",
    description: "{{ $addon->name }} Subscription",
    order_id: "{{ $order['id'] }}",
    handler: function (response) {
        document.getElementById('rzp-btn').disabled = true;
        document.getElementById('rzp-btn').innerHTML = '<i class="ti ti-loader"></i> Verifying...';

        fetch('{{ route("tenant.addons.success") }}?addon_key={{ $addon->key }}&payment_id=' + response.razorpay_payment_id + '&order_id=' + response.razorpay_order_id + '&signature=' + response.razorpay_signature, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(res) {
            window.location.href = '{{ route("tenant.addons.success") }}?addon_key={{ $addon->key }}';
        }).catch(function() {
            window.location.href = '{{ route("tenant.addons.success") }}?addon_key={{ $addon->key }}';
        });
    },
    prefill: {
        name: "{{ $tenant->name }}",
        email: "{{ auth()->user()->email }}",
    },
    theme: { color: "#0d9488" },
    modal: { ondismiss: function() { console.log('Payment dismissed'); } }
};

var rzp = new Razorpay(options);
document.getElementById('rzp-btn').disabled = false;
document.getElementById('rzp-btn').innerHTML = '<i class="ti ti-credit-card"></i> Pay ₹{{ number_format($addon->price * 1.18, 0) }} Now';
document.getElementById('rzp-btn').onclick = function(e) { e.preventDefault(); rzp.open(); };
</script>
@endsection
