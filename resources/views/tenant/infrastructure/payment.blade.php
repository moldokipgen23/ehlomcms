@extends('tenant.layouts.dashboard')

@section('title', 'Secure Payment - {{ $product->name }}')

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endpush

@section('content')
<div class="eos-row" style="max-width:500px;margin:0 auto;">
    <div class="eos-card">
        <div class="eos-card-header">
            <div class="eos-card-title">Secure Checkout</div>
        </div>
        <div class="eos-card-body">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <i class="ti {{ $product->category === 'domain' ? 'ti ti-world' : 'ti ti-server' }}" style="font-size:32px;color:var(--accent-teal);"></i>
                <div>
                    <div style="font-size:18px;font-weight:600;">{{ $product->name }}</div>
                    <div style="font-size:13px;color:var(--text-muted);">{{ ucfirst($product->category) }} - {{ ucfirst($product->billing_cycle) }} billing</div>
                </div>
            </div>

            <div style="background:var(--bg-hover);border-radius:8px;padding:16px;margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;font-size:14px;">
                    <span style="color:var(--text-muted);">Price ({{ ucfirst($product->billing_cycle) }})</span>
                    <span style="font-weight:600;">₹{{ number_format($product->price, 0) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;margin-top:8px;">
                    <span style="color:var(--text-muted);">GST (18%)</span>
                    <span style="font-weight:600;">₹{{ number_format($product->price * 0.18, 0) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:16px;margin-top:12px;font-weight:700;border-top:1px solid var(--border);padding-top:12px;">
                    <span>Total</span>
                    <span>₹{{ number_format($product->price * 1.18, 0) }}</span>
                </div>
            </div>

            <button id="rzp-btn" class="eos-btn eos-btn-primary" style="width:100%;padding:16px;font-size:16px;font-weight:600;" disabled>
                <i class="ti ti-lock"></i> Initializing...
            </button>

            <p style="font-size:12px;color:var(--text-muted);text-align:center;margin-top:16px;">
                Secured by Razorpay. We don't store card details.
            </p>
        </div>
    </div>
</div>

<script>
var options = {
    key: "{{ $paymentSetting->razorpay_key_id }}",
    amount: {{ (int) ($product->price * 1.18 * 100) }},
    currency: "INR",
    name: "{{ $tenant->name }}",
    description: "{{ $product->name }} - {{ ucfirst($product->billing_cycle) }}",
    order_id: "{{ $order['id'] }}",
    handler: function (response) {
        document.getElementById('rzp-btn').disabled = true;
        document.getElementById('rzp-btn').innerHTML = '<i class="ti ti-loader"></i> Verifying...';

        window.location.href = '{{ route("tenant.infrastructure.success") }}?product_id={{ $product->id }}&type={{ $product->category }}&payment_id=' + response.razorpay_payment_id + '&order_id=' + response.razorpay_order_id + '&signature=' + response.razorpay_signature;
    },
    prefill: {
        name: "{{ $tenant->name }}",
        email: "{{ auth()->user()->email ?? '' }}",
    },
    theme: { color: "#0d9488" },
    modal: { ondismiss: function() { console.log('Payment dismissed'); } }
};

var rzp = new Razorpay(options);
document.getElementById('rzp-btn').disabled = false;
document.getElementById('rzp-btn').innerHTML = '<i class="ti ti-credit-card"></i> Pay ₹{{ number_format($product->price * 1.18, 0) }} Now';
document.getElementById('rzp-btn').onclick = function(e) { e.preventDefault(); rzp.open(); };
</script>
@endsection