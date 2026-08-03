<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name }} — Pay</title>
    @if (($tenant->theme_settings['favicon'] ?? null))
        <link rel="icon" href="{{ Storage::url($tenant->theme_settings['favicon']) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .tp-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .tp-pay-card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 14px; padding: 32px; max-width: 400px; width: 100%; text-align: center; }
        .tp-pay-amount { font-size: 28px; font-weight: 700; color: var(--tp-accent, var(--accent-teal)); margin: 16px 0 8px; }
        .tp-pay-label { font-size: 13px; color: var(--text-muted); }
        .eos-btn { display: inline-flex; align-items: center; gap: 6px; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
        .eos-btn-primary { background: var(--tp-accent, var(--accent-teal)); color: #fff; }
        .eos-btn-primary:hover { opacity: .9; }
        .eos-btn:disabled { opacity: .5; cursor: not-allowed; }
    </style>
</head>
<body class="antialiased">
    @php $ts = $tenant->theme_settings ?? []; $accent = $ts['accent_color'] ?? '#4f8ef7'; @endphp
    <div class="tp-wrap" style="--tp-accent: {{ $accent }};">
        <div class="tp-pay-card">
            <i class="ti ti-credit-card" style="font-size:40px;color:var(--tp-accent);"></i>
            <div class="tp-pay-amount">₹{{ number_format($order->amount, 2) }}</div>
            <div class="tp-pay-label">Complete your payment for order {{ $order->order_id }}</div>
            <button type="button" id="rzp-btn" class="eos-btn eos-btn-primary" style="margin-top:20px;">
                <i class="ti ti-shield-lock"></i> Pay Now
            </button>
            <div id="rzp-processing" style="display:none;margin-top:16px;font-size:13px;color:var(--text-muted);">
                <i class="ti ti-loader ti-spin"></i> Processing payment...
            </div>
        </div>
    </div>

    <script>
        document.getElementById('rzp-btn').addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader ti-spin"></i> Opening...';
            document.getElementById('rzp-processing').style.display = 'block';

            const options = {
                key: '{{ $paymentSetting->api_key }}',
                amount: {{ (int)($order->amount * 100) }},
                currency: 'INR',
                order_id: '{{ $order->payment_order_id }}',
                name: '{{ $tenant->name }}',
                description: 'Order {{ $order->order_id }}',
                notes: {
                    order_id: '{{ $order->id }}',
                    tenant_id: '{{ $tenant->id }}'
                },
                handler: async function (response) {
                    try {
                        const verification = await fetch('{{ route('tenant.checkout.pay.verify', $order->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(response),
                        });
                        const payload = await verification.json();
                        if (!verification.ok || !payload.redirect) throw new Error(payload.message || 'Payment verification failed.');
                        window.location.href = payload.redirect;
                    } catch (error) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ti ti-shield-lock"></i> Pay Now';
                        document.getElementById('rzp-processing').style.display = 'none';
                        alert(error.message || 'We could not verify your payment. Please contact the store.');
                    }
                },
                modal: {
                    ondismiss: function () {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ti ti-shield-lock"></i> Pay Now';
                        document.getElementById('rzp-processing').style.display = 'none';
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();
        });
    </script>
</body>
</html>
