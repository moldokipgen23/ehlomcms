<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure payment - {{ $tenant->name }}</title>
    @if (($tenant->theme_settings['favicon'] ?? null))
        <link rel="icon" href="{{ Storage::url($tenant->theme_settings['favicon']) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root { font-family: 'DM Sans', sans-serif; color: #162235; background: #f5f7fa; }
        * { box-sizing: border-box; } body { margin: 0; min-height: 100vh; background: #f5f7fa; color: #162235; }
        .site-head { height: 74px; padding: 0 clamp(20px, 5vw, 72px); display: flex; align-items: center; justify-content: space-between; background: #fff; border-bottom: 1px solid #e4eaf1; }
        .brand { color: var(--brand); font-size: 17px; font-weight: 700; letter-spacing: .01em; } .back { color: #526276; font-size: 13px; text-decoration: none; }
        .page { min-height: calc(100vh - 74px); display: grid; place-items: center; padding: 32px 20px; }
        .card { width: min(100%, 510px); border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; padding: clamp(28px, 5vw, 46px); box-shadow: 0 18px 50px rgba(22,34,53,.08); }
        .eyebrow { color: var(--brand); font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; } h1 { margin: 12px 0 8px; font: 600 clamp(28px, 5vw, 40px) 'Playfair Display', serif; }
        .copy { margin: 0; color: #657386; line-height: 1.6; font-size: 14px; }.amount { margin: 28px 0; padding: 22px 0; border-block: 1px solid #e7edf4; color: #132033; font-size: 32px; font-weight: 700; }
        .pay { width: 100%; border: 0; border-radius: 8px; padding: 15px 18px; background: var(--brand); color: #fff; font: 700 14px 'DM Sans',sans-serif; cursor: pointer; } .pay:disabled { opacity: .65; cursor: wait; }
        .note { margin: 16px 0 0; color: #718096; font-size: 12px; line-height: 1.6; text-align: center; }
    </style>
</head>
<body>
@php $settings = $tenant->theme_settings ?? []; $brand = $settings['accent_color'] ?? '#2563eb'; @endphp
<header class="site-head" style="--brand: {{ $brand }};"><div class="brand">{{ $tenant->name }}</div><a class="back" href="{{ route('tenant.cart') }}"><i class="ti ti-arrow-left"></i> Back to cart</a></header>
<main class="page" style="--brand: {{ $brand }};"><section class="card"><div class="eyebrow">Secure checkout</div><h1>Complete payment</h1><p class="copy">Order {{ $order->order_id }}</p><div class="amount">₹{{ number_format($order->amount, 2) }}</div><button id="rzp-btn" class="pay" type="button">Pay securely</button><p id="processing" class="note" hidden>Verifying your payment securely...</p><p class="note">Payments are processed securely through Razorpay.</p></section></main>
<script>
document.getElementById('rzp-btn').addEventListener('click', function () {
    const button = this; button.disabled = true; button.textContent = 'Opening payment...';
    const checkout = new Razorpay({ key: @json($paymentSetting->api_key), amount: {{ (int) round($order->amount * 100) }}, currency: 'INR', order_id: @json($order->payment_order_id), name: @json($tenant->name), description: @json('Order ' . $order->order_id), handler: async (response) => { document.getElementById('processing').hidden = false; try { const verified = await fetch(@json(route('tenant.checkout.pay.verify', $order->id)), { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:JSON.stringify(response) }); const data = await verified.json(); if (!verified.ok || !data.redirect) throw new Error(data.message || 'Payment verification failed.'); window.location.assign(data.redirect); } catch (error) { button.disabled=false; button.textContent='Pay securely'; document.getElementById('processing').hidden=true; alert(error.message || 'We could not verify this payment.'); } }, modal:{ ondismiss:()=>{ button.disabled=false; button.textContent='Pay securely'; } } }); checkout.open();
});
</script>
</body>
</html>
