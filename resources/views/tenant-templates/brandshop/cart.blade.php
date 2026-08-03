<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name }} — Cart</title>
    @if (($tenant->theme_settings['favicon'] ?? null))
        <link rel="icon" href="{{ Storage::url($tenant->theme_settings['favicon']) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .tp-wrap { min-height: 100vh; display: flex; flex-direction: column; }
        .tp-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 32px; border-bottom: 1px solid var(--border); background: var(--bg-card); }
        .tp-header-l { display: flex; align-items: center; gap: 12px; }
        .tp-back { color: var(--text-muted); text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 4px; }
        .tp-back:hover { color: var(--text-primary); }
        .tp-header-cart { position: relative; color: var(--text-secondary); text-decoration: none; }
        .tp-badge { position: absolute; top: -6px; right: -8px; background: var(--tp-accent, var(--accent-teal)); color: #fff; font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .tp-section { padding: 32px; max-width: 800px; margin: 0 auto; width: 100%; }
        .tp-section-title { font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 20px; }
        .tp-empty { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .tp-empty i { font-size: 48px; display: block; margin-bottom: 12px; }
        .tp-cart-item { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--border); }
        .tp-cart-img { width: 64px; height: 64px; border-radius: 8px; object-fit: cover; background: var(--bg-hover); border: 1px solid var(--border); }
        .tp-cart-info { flex: 1; min-width: 0; }
        .tp-cart-name { font-size: 14px; font-weight: 600; color: var(--text-primary); }
        .tp-cart-price { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .tp-cart-actions { display: flex; align-items: center; gap: 8px; }
        .tp-qty-form { display: flex; align-items: center; gap: 6px; }
        .tp-qty-input { width: 50px; text-align: center; padding: 4px 6px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-card); color: var(--text-primary); font-size: 13px; }
        .tp-qty-btn, .tp-remove-btn { background: none; border: 1px solid var(--border); border-radius: 6px; padding: 4px 10px; cursor: pointer; color: var(--text-secondary); font-size: 13px; }
        .tp-qty-btn:hover { background: var(--bg-hover); }
        .tp-remove-btn { color: var(--accent-red); border-color: transparent; }
        .tp-remove-btn:hover { background: rgba(239,68,68,0.08); }
        .tp-cart-subtotal { font-size: 13px; font-weight: 600; color: var(--text-primary); white-space: nowrap; }
        .tp-total-bar { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; margin-top: 8px; }
        .tp-total-label { font-size: 14px; font-weight: 600; color: var(--text-primary); }
        .tp-total-amount { font-size: 20px; font-weight: 700; color: var(--tp-accent, var(--accent-teal)); }
        .tp-actions { display: flex; gap: 10px; margin-top: 16px; }
        .eos-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
        .eos-btn-primary { background: var(--tp-accent, var(--accent-teal)); color: #fff; }
        .eos-btn-primary:hover { opacity: .9; }
        .eos-btn-secondary { background: var(--bg-hover); color: var(--text-primary); border: 1px solid var(--border); }
        .eos-btn-secondary:hover { background: var(--border); }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); margin-top: auto; }
    </style>
</head>
<body class="antialiased">
    @php
        $ts = $tenant->theme_settings ?? [];
        $accent = $ts['accent_color'] ?? '#4f8ef7';
        $hasCheckout = $tenant->hasModule('checkout');
    @endphp
    <div class="tp-wrap" style="--tp-accent: {{ $accent }};">
        <div class="tp-header">
            <div class="tp-header-l">
                <a href="{{ route('tenant.home') }}" class="tp-back"><i class="ti ti-arrow-left"></i> Back to store</a>
            </div>
            <a href="{{ route('tenant.cart') }}" class="tp-header-cart">
                <i class="ti ti-shopping-cart" style="font-size:22px;"></i>
                @if ($count > 0)<span class="tp-badge">{{ $count }}</span>@endif
            </a>
        </div>

        <div class="tp-section">
            <div class="tp-section-title">Shopping Cart</div>

            @if (empty($cart))
                <div class="tp-empty">
                    <i class="ti ti-shopping-cart-off"></i>
                    <p>Your cart is empty.</p>
                    <a href="{{ route('tenant.home') }}" class="eos-btn eos-btn-primary" style="display:inline-flex;margin-top:12px;">Continue Shopping</a>
                </div>
            @else
                @foreach ($cart as $id => $item)
                    @php $p = $item['product']; if (!$p) continue; @endphp
                    <div class="tp-cart-item">
                        @if ($p->main_image)
                            <img class="tp-cart-img" src="{{ Storage::url($p->main_image) }}" alt="{{ $p->name }}">
                        @else
                            <div class="tp-cart-img" style="display:flex;align-items:center;justify-content:center;color:var(--text-dim);"><i class="ti ti-photo-off"></i></div>
                        @endif
                        <div class="tp-cart-info">
                            <div class="tp-cart-name">{{ $p->name }}</div>
                            @if (!empty($item['variant']))
                                <div class="tp-cart-price">
                                    {{ $item['variant']->color?->color_name }}
                                    @if ($item['variant']->size) / {{ $item['variant']->size->size_label }} @endif
                                </div>
                            @endif
                            <div class="tp-cart-price">₹{{ number_format($item['unit_price'] ?? $p->price, 2) }} each</div>
                        </div>
                        <div class="tp-cart-actions">
                            <form action="{{ route('tenant.cart.update', $p) }}" method="POST" class="tp-qty-form">
                                @csrf
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="tp-qty-input" onchange="this.form.submit()">
                            </form>
                            <form action="{{ route('tenant.cart.remove', $p) }}" method="POST">
                                @csrf
                                <button type="submit" class="tp-remove-btn" title="Remove"><i class="ti ti-trash"></i></button>
                            </form>
                        </div>
                        <div class="tp-cart-subtotal">₹{{ number_format($item['subtotal'], 2) }}</div>
                    </div>
                @endforeach

                <div class="tp-total-bar">
                    <span class="tp-total-label">Total</span>
                    <span class="tp-total-amount">₹{{ number_format($total, 2) }}</span>
                </div>

                <div class="tp-actions">
                    @if ($hasCheckout)
                        <a href="{{ route('tenant.checkout') }}" class="eos-btn eos-btn-primary"><i class="ti ti-credit-card"></i> Proceed to Checkout</a>
                    @else
                        <span class="eos-btn eos-btn-secondary" style="opacity:0.65;cursor:not-allowed;"><i class="ti ti-credit-card-off"></i> Checkout disabled</span>
                    @endif
                    <a href="{{ route('tenant.home') }}" class="eos-btn eos-btn-secondary">Continue Shopping</a>
                </div>
            @endif
        </div>

        <div class="tp-foot">{{ $tenant->name }} &middot; Powered by Ehlom OS</div>
    </div>
</body>
</html>
