<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name }} — Order Confirmed</title>
    @if (($tenant->theme_settings['favicon'] ?? null))
        <link rel="icon" href="{{ Storage::url($tenant->theme_settings['favicon']) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .tp-wrap { min-height: 100vh; display: flex; flex-direction: column; }
        .tp-body { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px; }
        .tp-confirm-card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 14px; padding: 40px; max-width: 520px; width: 100%; text-align: center; }
        .tp-icon { font-size: 56px; color: #22c55e; display: block; margin-bottom: 12px; }
        .tp-icon.pending { color: #f59e0b; }
        .tp-title { font-size: 20px; font-weight: 700; color: var(--text-primary); font-family: 'Syne', sans-serif; }
        .tp-order-num { font-size: 13px; color: var(--text-muted); margin: 8px 0 20px; }
        .tp-order-num strong { color: var(--text-primary); }
        .tp-details { text-align: left; margin: 16px 0; }
        .tp-detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
        .tp-detail-label { color: var(--text-muted); }
        .tp-detail-value { color: var(--text-primary); font-weight: 600; }
        .eos-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
        .eos-btn-primary { background: var(--tp-accent, var(--accent-teal)); color: #fff; }
        .eos-btn-primary:hover { opacity: .9; }
        .eos-btn-secondary { background: var(--bg-hover); color: var(--text-primary); border: 1px solid var(--border); }
        .eos-btn-secondary:hover { background: var(--border); }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); }
    </style>
</head>
<body class="antialiased">
    @php
        $ts = $tenant->theme_settings ?? [];
        $accent = $ts['accent_color'] ?? '#4f8ef7';
        $isPaid = in_array($order->status, ['paid', 'confirmed'], true)
            || in_array($order->payment_status, ['paid', 'captured'], true);
        $isPending = $order->status === 'pending' || $order->status === 'awaiting_payment';
    @endphp
    <div class="tp-wrap" style="--tp-accent: {{ $accent }};">
        <div class="tp-body">
            <div class="tp-confirm-card">
                @if ($isPaid)
                    <i class="ti ti-circle-check tp-icon"></i>
                    <div class="tp-title">Payment Successful!</div>
                @elseif ($order->status === 'pending')
                    <i class="ti ti-truck-delivery tp-icon" style="color:var(--tp-accent);"></i>
                    <div class="tp-title">Order Placed!</div>
                @else
                    <i class="ti ti-clock tp-icon pending"></i>
                    <div class="tp-title">Payment Pending</div>
                @endif

                <div class="tp-order-num">
                    Order Reference: <strong>{{ $order->order_id }}</strong>
                </div>

                @if ($order->payment_id)
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">
                        Payment ID: {{ $order->payment_id }}
                    </div>
                @endif

                <div class="tp-details">
                    <div class="tp-detail-row">
                        <span class="tp-detail-label">Status</span>
                        <span class="tp-detail-value" style="text-transform:capitalize;">{{ $order->status }}</span>
                    </div>
                    <div class="tp-detail-row">
                        <span class="tp-detail-label">Payment Method</span>
                        <span class="tp-detail-value" style="text-transform:capitalize;">{{ $order->payment_method }}</span>
                    </div>
                    <div class="tp-detail-row">
                        <span class="tp-detail-label">Total</span>
                        <span class="tp-detail-value">₹{{ number_format($order->amount, 2) }}</span>
                    </div>
                    <div class="tp-detail-row">
                        <span class="tp-detail-label">Shipping</span>
                        <span class="tp-detail-value" style="font-weight:400;font-size:12px;">{{ $order->shipping_name }}, {{ $order->shipping_address }}</span>
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:16px;justify-content:center;flex-wrap:wrap;">
                    <a href="{{ route('tenant.home') }}" class="eos-btn eos-btn-primary">
                        <i class="ti ti-arrow-left"></i> Back to Store
                    </a>
                    <a href="{{ route('tenant.track.lookup') }}?order_id={{ urlencode($order->order_id) }}&phone={{ urlencode($order->shipping_phone) }}" class="eos-btn eos-btn-secondary">
                        <i class="ti ti-package"></i> Track Order
                    </a>
                </div>
            </div>
        </div>
        <div class="tp-foot">{{ $tenant->name }} &middot; Powered by Ehlom OS</div>
    </div>
</body>
</html>
