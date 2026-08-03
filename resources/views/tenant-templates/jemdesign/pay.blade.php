<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment - {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root { color-scheme: dark; font-family: Manrope, sans-serif; }
        * { box-sizing: border-box; } body { margin:0; min-height:100vh; background:#0b0b0c; color:#f4f0e7; }
        .head { padding:22px clamp(20px,5vw,72px); display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,.1); }
        .brand { color:#c99a4a; font:600 18px 'Playfair Display',serif; letter-spacing:.12em; text-transform:uppercase; } .back { color:#ddd4c5; text-decoration:none; font-size:13px; }
        .page { min-height:calc(100vh - 70px); display:grid; place-items:center; padding:32px 20px; } .card { width:min(100%,510px); padding:clamp(28px,5vw,52px); border:1px solid rgba(201,154,74,.35); background:#121214; }
        .eyebrow { color:#c99a4a; font:500 11px 'DM Mono',monospace; letter-spacing:.18em; text-transform:uppercase; } h1 { font:500 clamp(30px,5vw,48px) 'Playfair Display',serif; margin:14px 0 8px; }
        .copy { color:#beb6aa; font-size:14px; line-height:1.7; } .amount { margin:28px 0; padding:20px 0; border-block:1px solid rgba(255,255,255,.12); font-size:30px; color:#f9f5ec; }
        .pay { width:100%; border:0; padding:16px; background:#c99a4a; color:#15110d; font:700 13px Manrope,sans-serif; letter-spacing:.06em; text-transform:uppercase; cursor:pointer; } .pay:disabled { opacity:.6; cursor:wait; }
        .note { margin-top:18px; color:#8e877d; font-size:12px; text-align:center; line-height:1.6; }
    </style>
</head>
<body>
    <header class="head"><div class="brand">Jem Designs &amp; Co</div><a class="back" href="{{ route('tenant.cart') }}">Back to bag</a></header>
    <main class="page"><section class="card"><div class="eyebrow">Secure payment</div><h1>Complete your order</h1><p class="copy">Order {{ $order->order_id }}</p><div class="amount">₹{{ number_format($order->amount, 2) }}</div><button id="rzp-btn" class="pay" type="button">Pay securely</button><p id="processing" class="note" hidden>Verifying your payment securely...</p><p class="note">Payment is processed by Razorpay. Your order is confirmed only after verification.</p></section></main>
    <script>
        document.getElementById('rzp-btn').addEventListener('click', function () {
            const button = this; button.disabled = true; button.textContent = 'Opening payment...';
            const checkout = new Razorpay({ key: @json($paymentSetting->api_key), amount: {{ (int) round($order->amount * 100) }}, currency: 'INR', order_id: @json($order->payment_order_id), name: @json($tenant->name), description: @json('Order ' . $order->order_id), handler: async (response) => { document.getElementById('processing').hidden = false; try { const verified = await fetch(@json(route('tenant.checkout.pay.verify', $order->id)), { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:JSON.stringify(response) }); const data = await verified.json(); if (!verified.ok || !data.redirect) throw new Error(data.message || 'Payment verification failed.'); window.location.assign(data.redirect); } catch (error) { button.disabled=false; button.textContent='Pay securely'; document.getElementById('processing').hidden=true; alert(error.message || 'We could not verify this payment.'); } }, modal:{ ondismiss:()=>{ button.disabled=false; button.textContent='Pay securely'; } } }); checkout.open();
        });
    </script>
</body>
</html>
