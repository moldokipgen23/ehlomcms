<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name }} — Track Order</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .tp-wrap { min-height: 100vh; display: flex; flex-direction: column; }
        .tp-header { display: flex; align-items: center; padding: 16px 32px; border-bottom: 1px solid var(--border); background: var(--bg-card); }
        .tp-back { color: var(--text-muted); text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 4px; }
        .tp-back:hover { color: var(--text-primary); }
        .tp-body { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px; }
        .tp-card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 14px; padding: 32px; max-width: 480px; width: 100%; }
        .tp-title { font-size: 18px; font-weight: 700; font-family: 'Syne', sans-serif; color: var(--text-primary); margin-bottom: 20px; text-align: center; }
        .tp-field { margin-bottom: 14px; }
        .tp-field label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px; }
        .tp-field input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-card); color: var(--text-primary); font-size: 14px; font-family: inherit; }
        .tp-field input:focus { outline: none; border-color: var(--tp-accent, var(--accent-teal)); box-shadow: 0 0 0 3px rgba(79,142,247,.15); }
        .tp-error { color: #ef4444; font-size: 11px; margin-top: 3px; }
        .eos-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; width: 100%; justify-content: center; }
        .eos-btn-primary { background: var(--tp-accent, var(--accent-teal)); color: #fff; }
        .eos-btn-primary:hover { opacity: .9; }
        .tp-result { margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
        .tp-result-icon { font-size: 40px; display: block; margin-bottom: 8px; }
        .tp-result-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 12px; }
        .tp-detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
        .tp-detail-label { color: var(--text-muted); }
        .tp-detail-value { color: var(--text-primary); font-weight: 600; }
        .tp-not-found { text-align: center; color: var(--text-muted); margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); }
    </style>
</head>
<body class="antialiased">
    @php $ts = $tenant->theme_settings ?? []; $accent = $ts['accent_color'] ?? '#4f8ef7'; @endphp
    <div class="tp-wrap" style="--tp-accent: {{ $accent }};">
        <div class="tp-header">
            <a href="{{ route('tenant.home') }}" class="tp-back"><i class="ti ti-arrow-left"></i> Back to store</a>
        </div>
        <div class="tp-body">
            <div class="tp-card">
                <div class="tp-title"><i class="ti ti-package" style="color:var(--tp-accent);"></i> Track Your Order</div>

                <form method="GET" action="{{ route('tenant.track') }}">
                    <div class="tp-field">
                        <label for="order_id">Order Number</label>
                        <input type="text" name="order_id" id="order_id" value="{{ request('order_id') }}" placeholder="e.g. ORD-1-..." required>
                    </div>
                    <div class="tp-field">
                        <label for="phone">Phone Number (used at checkout)</label>
                        <input type="text" name="phone" id="phone" value="{{ request('phone') }}" placeholder="e.g. 9876543210" required>
                    </div>
                    <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-search"></i> Look Up Order</button>
                </form>

                @if (isset($notFound))
                    <div class="tp-not-found">
                        <i class="ti ti-search-off" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                        <p>No order found. Please check your order number and phone number.</p>
                    </div>
                @elseif (isset($order))
                    <div class="tp-result">
                        <i class="tp-result-icon ti {{ $order->status === 'delivered' ? 'ti-circle-check' : ($order->status === 'cancelled' ? 'ti-circle-x' : 'ti-clock') }}" style="color:{{ $order->status === 'delivered' || $order->status === 'paid' ? '#22c55e' : ($order->status === 'cancelled' ? '#ef4444' : 'var(--tp-accent)') }};"></i>
                        <div class="tp-result-title">Order {{ $order->order_id }}</div>

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

                        @if ($order->items->count())
                            @foreach ($order->items as $item)
                                <div class="tp-detail-row">
                                    <span class="tp-detail-label">{{ $item->product?->name ?? 'Product' }}</span>
                                    <span class="tp-detail-value" style="font-weight:400;">x{{ $item->quantity }} &middot; ₹{{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                                </div>
                            @endforeach
                        @endif

                        <div class="tp-detail-row">
                            <span class="tp-detail-label">Shipping</span>
                            <span class="tp-detail-value" style="font-weight:400;font-size:12px;">{{ $order->shipping_name }}, {{ $order->shipping_address }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="tp-foot">{{ $tenant->name }} &middot; Powered by Ehlom OS</div>
    </div>
</body>
</html>
