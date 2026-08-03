<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }} - Order Confirmed</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/jemdesign-storefront.css') }}">
    <style>
        body { background: var(--black); color: var(--white); }
        .jem-confirm { min-height: 100vh; display: grid; place-items: center; padding: 32px; }
        .jem-confirm-card { width: min(560px, 100%); border: 1px solid rgba(255,255,255,.08); background: linear-gradient(135deg, rgba(255,255,255,.045), rgba(201,160,78,.075)); padding: clamp(28px, 6vw, 52px); text-align: center; }
        .jem-confirm-kicker { color: var(--gold); font-size: 11px; letter-spacing: .24em; text-transform: uppercase; margin-bottom: 18px; }
        .jem-confirm-title { font-family: var(--serif); font-size: clamp(40px, 7vw, 72px); font-weight: 300; line-height: 1; margin-bottom: 18px; }
        .jem-confirm-copy { color: var(--gray); line-height: 1.8; margin-bottom: 28px; }
        .jem-detail { display: flex; justify-content: space-between; gap: 18px; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,.08); color: var(--white-dim); text-align: left; }
        .jem-detail span:first-child { color: var(--gray); }
        .jem-confirm-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 28px; }
    </style>
</head>
<body>
<main class="jem-confirm">
    <section class="jem-confirm-card">
        <div class="jem-confirm-kicker">{{ $order->status === 'awaiting_payment' ? 'Payment Pending' : 'Order Received' }}</div>
        <h1 class="jem-confirm-title">{{ $order->status === 'awaiting_payment' ? 'Almost Done' : 'Thank You' }}</h1>
        <p class="jem-confirm-copy">Your Jem Designs order has been recorded. Keep this reference for tracking and support.</p>
        <div class="jem-detail"><span>Order</span><strong>{{ $order->order_id }}</strong></div>
        <div class="jem-detail"><span>Status</span><strong style="text-transform:capitalize;">{{ $order->status }}</strong></div>
        <div class="jem-detail"><span>Payment</span><strong style="text-transform:capitalize;">{{ $order->payment_method }}</strong></div>
        <div class="jem-detail"><span>Total</span><strong>₹{{ number_format($order->amount, 2) }}</strong></div>
        <div class="jem-confirm-actions">
            <a href="{{ route('tenant.home') }}" class="btn btn--gold">Back to Store</a>
            <a href="{{ route('tenant.track.lookup') }}?order_id={{ urlencode($order->order_id) }}&phone={{ urlencode($order->shipping_phone) }}" class="btn btn--outline">Track Order</a>
        </div>
    </section>
</main>
</body>
</html>
