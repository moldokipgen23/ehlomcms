<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name }} - Cart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/jemdesign-storefront.css') }}">
    <style>
        .jem-checkout-shell { min-height: 100vh; background: var(--black); color: var(--white); }
        .jem-checkout-head { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; justify-content: space-between; padding: 22px clamp(18px, 5vw, 56px); background: rgba(11,11,12,.9); border-bottom: 1px solid rgba(255,255,255,.08); backdrop-filter: blur(18px); }
        .jem-wordmark { font-family: var(--serif); font-size: 32px; font-style: italic; color: var(--white); }
        .jem-back { color: var(--gray); font-size: 11px; letter-spacing: .18em; text-transform: uppercase; }
        .jem-back:hover { color: var(--gold); }
        .jem-page { width: min(1080px, calc(100% - 32px)); margin: 0 auto; padding: 58px 0 90px; }
        .jem-page-title { font-family: var(--serif); font-size: clamp(38px, 6vw, 72px); font-weight: 300; margin-bottom: 12px; }
        .jem-page-sub { color: var(--gray); line-height: 1.7; margin-bottom: 34px; }
        .jem-panel { border: 1px solid rgba(255,255,255,.08); background: linear-gradient(135deg, rgba(255,255,255,.045), rgba(201,160,78,.055)); }
        .jem-cart-item { display: grid; grid-template-columns: 92px 1fr auto; gap: 18px; align-items: center; padding: 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .jem-cart-item:last-child { border-bottom: 0; }
        .jem-cart-img { width: 92px; height: 116px; object-fit: cover; background: var(--black-card); }
        .jem-cart-name { font-family: var(--serif); font-size: 23px; line-height: 1.15; }
        .jem-cart-meta { color: var(--gray); font-size: 12px; margin-top: 6px; line-height: 1.6; }
        .jem-cart-actions { display: flex; gap: 8px; align-items: center; justify-content: flex-end; margin-top: 12px; }
        .jem-qty { width: 58px; min-height: 40px; text-align: center; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.14); color: var(--white); }
        .jem-small-btn { min-height: 40px; padding: 0 14px; border: 1px solid rgba(255,255,255,.14); color: var(--white-dim); font-size: 10px; letter-spacing: .12em; text-transform: uppercase; }
        .jem-small-btn:hover { border-color: var(--gold); color: var(--gold); }
        .jem-total { display: flex; align-items: center; justify-content: space-between; padding: 24px; border-top: 1px solid rgba(201,160,78,.2); }
        .jem-total span:first-child { color: var(--gray); text-transform: uppercase; letter-spacing: .18em; font-size: 11px; }
        .jem-total span:last-child { font-size: 26px; color: var(--gold); }
        .jem-cart-footer { display: flex; gap: 12px; justify-content: flex-end; padding: 22px 24px 24px; }
        .jem-empty { padding: 70px 24px; text-align: center; color: var(--gray); }
        @media (max-width: 680px) {
            .jem-cart-item { grid-template-columns: 76px 1fr; }
            .jem-cart-img { width: 76px; height: 96px; }
            .jem-line-price { grid-column: 1 / -1; text-align: right; }
            .jem-cart-footer { flex-direction: column; }
            .jem-cart-footer .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="jem-checkout-shell">
    <header class="jem-checkout-head">
        <a href="{{ route('tenant.home') }}" class="jem-wordmark">jem</a>
        <a href="{{ route('tenant.home') }}#shop" class="jem-back">Continue Shopping</a>
    </header>

    <main class="jem-page">
        <h1 class="jem-page-title">Your Bag</h1>
        <p class="jem-page-sub">Review your selected pieces before checkout.</p>

        <section class="jem-panel">
            @if (empty($cart))
                <div class="jem-empty">
                    <p>Your bag is empty.</p>
                    <a href="{{ route('tenant.home') }}#shop" class="btn btn--gold" style="margin-top:18px;">Shop Collection</a>
                </div>
            @else
                @foreach ($cart as $item)
                    @php $p = $item['product']; if (!$p) continue; @endphp
                    <article class="jem-cart-item">
                        @if ($p->main_image)
                            <img class="jem-cart-img" src="{{ Storage::url($p->main_image) }}" alt="{{ $p->name }}">
                        @else
                            <div class="jem-cart-img"></div>
                        @endif
                        <div>
                            <a href="{{ route('tenant.product.show', $p->slug) }}" class="jem-cart-name">{{ $p->name }}</a>
                            <div class="jem-cart-meta">
                                ₹{{ number_format($item['unit_price'] ?? $p->price, 2) }} each
                                @if (!empty($item['variant']))
                                    <br>{{ $item['variant']->color?->color_name }} @if ($item['variant']->size) / {{ $item['variant']->size->size_label }} @endif
                                @endif
                            </div>
                            <div class="jem-cart-actions">
                                <form action="{{ route('tenant.cart.update', $p) }}" method="POST">
                                    @csrf
                                    <input class="jem-qty" type="number" name="quantity" min="1" value="{{ $item['quantity'] }}" onchange="this.form.submit()">
                                </form>
                                <form action="{{ route('tenant.cart.remove', $p) }}" method="POST">
                                    @csrf
                                    <button class="jem-small-btn" type="submit">Remove</button>
                                </form>
                            </div>
                        </div>
                        <div class="jem-line-price">₹{{ number_format($item['subtotal'], 2) }}</div>
                    </article>
                @endforeach
                <div class="jem-total">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($total, 2) }}</span>
                </div>
                <div class="jem-cart-footer">
                    <a href="{{ route('tenant.home') }}#shop" class="btn btn--outline">Continue Shopping</a>
                    @if ($tenant->hasModule('checkout'))
                        <a href="{{ route('tenant.checkout') }}" class="btn btn--gold">Checkout</a>
                    @endif
                </div>
            @endif
        </section>
    </main>
</div>
</body>
</html>
