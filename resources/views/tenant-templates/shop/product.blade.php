<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product->name }} — {{ $tenant->name }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($product->description ?: $tenant->name), 155) }}">
    @if (($tenant->theme_settings['favicon'] ?? null))
        <link rel="icon" href="{{ Storage::url($tenant->theme_settings['favicon']) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .tp-wrap { min-height: 100vh; display: flex; flex-direction: column; }
        .tp-topbar { position: sticky; top: 0; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 15px 28px; border-bottom: 1px solid var(--border); background: color-mix(in srgb, var(--bg-card) 92%, transparent); backdrop-filter: blur(14px); }
        .tp-brand { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; color: var(--text-primary); text-decoration: none; }
        .tp-nav { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .tp-nav a { color: var(--text-muted); text-decoration: none; font-size: 12px; font-weight: 800; }
        .tp-nav a:hover { color: var(--tp-accent, var(--accent-teal)); }
        .tp-shell { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 30px 0 72px; }
        .tp-crumbs { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; color: var(--text-muted); font-size: 12px; margin-bottom: 20px; }
        .tp-crumbs a { color: var(--text-muted); text-decoration: none; }
        .tp-crumbs a:hover { color: var(--tp-accent, var(--accent-teal)); }
        .tp-product { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(340px, .9fr); gap: 32px; align-items: start; }
        .tp-gallery { position: sticky; top: 82px; min-width: 0; }
        .tp-gallery-main { position: relative; overflow: hidden; border: 1px solid var(--border-card); border-radius: 12px; background: var(--bg-hover); aspect-ratio: 4 / 5; }
        .tp-gallery-track { display: flex; height: 100%; transition: transform .35s ease; }
        .tp-gallery-slide { flex: 0 0 100%; height: 100%; }
        .tp-gallery-slide img { width: 100%; height: 100%; object-fit: contain; object-position: center center; display: block; background: var(--bg-hover); }
        .tp-gallery-empty { height: 100%; display: grid; place-items: center; color: var(--text-dim); }
        .tp-gallery-btn { position: absolute; top: 50%; transform: translateY(-50%); width: 42px; height: 42px; border-radius: 999px; border: 1px solid rgba(255,255,255,.38); background: rgba(0,0,0,.42); color: #fff; display: grid; place-items: center; cursor: pointer; }
        .tp-gallery-btn:hover { background: var(--tp-accent, var(--accent-teal)); }
        .tp-gallery-btn.prev { left: 12px; }
        .tp-gallery-btn.next { right: 12px; }
        .tp-gallery-count { position: absolute; right: 12px; bottom: 12px; padding: 6px 9px; border-radius: 999px; background: rgba(0,0,0,.5); color: #fff; font-size: 11px; font-weight: 800; }
        .tp-thumbs { display: grid; grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); gap: 8px; margin-top: 10px; }
        .tp-thumb { height: 76px; border-radius: 9px; border: 2px solid transparent; background: var(--bg-hover); overflow: hidden; padding: 0; cursor: pointer; }
        .tp-thumb.active { border-color: var(--tp-accent, var(--accent-teal)); }
        .tp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .tp-info { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 14px; padding: clamp(20px, 4vw, 30px); }
        .tp-kicker { color: var(--tp-accent, var(--accent-teal)); font-size: 11px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px; }
        .tp-title { font-family: 'Syne', sans-serif; color: var(--text-primary); font-size: clamp(28px, 4vw, 46px); line-height: 1.05; margin: 0 0 12px; }
        .tp-price { color: var(--tp-accent, var(--accent-teal)); font-size: 24px; font-weight: 900; margin-bottom: 14px; }
        .tp-desc { color: var(--text-secondary); line-height: 1.75; font-size: 14px; margin-bottom: 22px; }
        .tp-fieldset { margin: 20px 0; }
        .tp-label { display: block; color: var(--text-muted); font-size: 11px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 9px; }
        .tp-swatches, .tp-sizes { display: flex; gap: 8px; flex-wrap: wrap; }
        .tp-swatch { width: 38px; height: 38px; border-radius: 999px; border: 2px solid var(--border); cursor: pointer; }
        .tp-swatch.active { border-color: var(--tp-accent, var(--accent-teal)); box-shadow: 0 0 0 3px color-mix(in srgb, var(--tp-accent, #2563eb) 20%, transparent); }
        .tp-size { min-width: 44px; min-height: 42px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-primary); cursor: pointer; font-weight: 800; }
        .tp-size.active { border-color: var(--tp-accent, var(--accent-teal)); background: color-mix(in srgb, var(--tp-accent, #2563eb) 12%, transparent); }
        .tp-qty { display: inline-flex; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
        .tp-qty button, .tp-qty span { width: 46px; height: 44px; display: grid; place-items: center; color: var(--text-primary); }
        .tp-qty button { border: 0; background: var(--bg-hover); cursor: pointer; font-size: 18px; }
        .tp-actions { display: grid; gap: 10px; margin: 22px 0; }
        .tp-actions-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .tp-btn { min-height: 48px; border-radius: 10px; border: 1px solid var(--border); display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; font-weight: 900; text-decoration: none; cursor: pointer; }
        .tp-btn.primary { background: var(--tp-accent, var(--accent-teal)); color: #fff; border-color: var(--tp-accent, var(--accent-teal)); }
        .tp-btn.secondary { background: var(--bg-hover); color: var(--text-primary); }
        .tp-details { margin-top: 20px; border-top: 1px solid var(--border); }
        .tp-detail { border-bottom: 1px solid var(--border); padding: 15px 0; }
        .tp-detail strong { display: block; color: var(--text-primary); font-size: 13px; margin-bottom: 5px; }
        .tp-detail p { color: var(--text-secondary); font-size: 13px; line-height: 1.7; margin: 0; }
        .tp-related { margin-top: 42px; }
        .tp-section-title { font-size: 13px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 16px; }
        .tp-related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 14px; }
        .tp-related-card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 12px; overflow: hidden; text-decoration: none; }
        .tp-related-card img { width: 100%; height: 160px; object-fit: cover; display: block; background: var(--bg-hover); }
        .tp-related-card div { padding: 12px; color: var(--text-primary); font-weight: 800; }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); margin-top: auto; }
        @media (max-width: 860px) {
            .tp-product { grid-template-columns: 1fr; }
            .tp-gallery { position: static; }
            .tp-topbar { align-items: flex-start; flex-direction: column; }
        }
        @media (max-width: 520px) {
            .tp-shell { width: min(100% - 20px, 1180px); padding-top: 18px; }
            .tp-info { padding: 18px; }
            .tp-actions-row { grid-template-columns: 1fr; }
            .tp-thumbs { display: flex; overflow-x: auto; padding-bottom: 4px; }
            .tp-thumb { flex: 0 0 66px; }
        }
    </style>
</head>
<body class="antialiased">
@php
    $ts = $tenant->theme_settings ?? [];
    $accent = $ts['accent_color'] ?? '#2563eb';
    $cartSession = session('tenant_cart_' . ($tenant->id ?? 'guest'), []);
    $cartCount = array_sum(array_column($cartSession, 'quantity'));
    $waNumber = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number ?? '');
    $images = collect([$product->main_image])
        ->merge($product->images->pluck('image_path'))
        ->merge($product->colors->flatMap(fn ($color) => $color->images->pluck('image_path')))
        ->filter()
        ->unique()
        ->map(fn ($path) => Storage::url($path))
        ->values();
    $variants = $product->variants->where('is_active', true)->mapWithKeys(fn ($variant) => [
        ($variant->tenant_product_color_id ?: 'base') . '_' . ($variant->tenant_product_size_id ?: 'base') => [
            'id' => $variant->id,
            'stock' => $variant->stock,
            'price' => (float) $variant->effective_price,
        ],
    ]);
@endphp
<div class="tp-wrap" style="--tp-accent: {{ $accent }};">
    <header class="tp-topbar">
        <a href="{{ route('tenant.home') }}" class="tp-brand">{{ $tenant->name }}</a>
        <nav class="tp-nav">
            <a href="{{ route('tenant.home') }}#catalog">Catalog</a>
            @if ($tenant->hasModule('wishlist'))<a href="{{ route('tenant.wishlist') }}">Wishlist</a>@endif
            @if ($tenant->hasModule('customer_accounts'))<a href="{{ route('tenant.customer.account') }}">Account</a>@endif
            @if ($tenant->hasModule('cart'))<a href="{{ route('tenant.cart') }}"><i class="ti ti-shopping-cart"></i> Cart {{ $cartCount ? '(' . $cartCount . ')' : '' }}</a>@endif
            <a href="{{ route('tenant.track') }}">Track Order</a>
        </nav>
    </header>

    <main class="tp-shell">
        <div class="tp-crumbs">
            <a href="{{ route('tenant.home') }}">Home</a><span>/</span>
            <a href="{{ route('tenant.home') }}#catalog">Catalog</a><span>/</span>
            <span>{{ $product->name }}</span>
        </div>

        <section class="tp-product">
            <div class="tp-gallery" id="tpGallery">
                <div class="tp-gallery-main">
                    @if ($images->count())
                        <div class="tp-gallery-track" id="tpGalleryTrack">
                            @foreach ($images as $image)
                                <div class="tp-gallery-slide"><img src="{{ $image }}" alt="{{ $product->name }} image {{ $loop->iteration }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}"></div>
                            @endforeach
                        </div>
                        @if ($images->count() > 1)
                            <button type="button" class="tp-gallery-btn prev" id="tpGalleryPrev" aria-label="Previous image"><i class="ti ti-chevron-left"></i></button>
                            <button type="button" class="tp-gallery-btn next" id="tpGalleryNext" aria-label="Next image"><i class="ti ti-chevron-right"></i></button>
                            <div class="tp-gallery-count" id="tpGalleryCount">1 / {{ $images->count() }}</div>
                        @endif
                    @else
                        <div class="tp-gallery-empty"><i class="ti ti-photo-off" style="font-size:42px;"></i></div>
                    @endif
                </div>
                @if ($images->count() > 1)
                    <div class="tp-thumbs">
                        @foreach ($images as $image)
                            <button type="button" class="tp-thumb {{ $loop->first ? 'active' : '' }}" data-index="{{ $loop->index }}"><img src="{{ $image }}" alt="{{ $product->name }} thumbnail {{ $loop->iteration }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>

            <article class="tp-info">
                <div class="tp-kicker">{{ $product->productCategory?->name ?? $product->collections->first()?->name ?? 'Product' }}</div>
                <h1 class="tp-title">{{ $product->name }}</h1>
                <div class="tp-price">₹{{ number_format($product->price, 2) }}</div>
                @if ($product->description)<div class="tp-desc">{{ $product->description }}</div>@endif

                @if ($product->colors->isNotEmpty())
                    <div class="tp-fieldset">
                        <span class="tp-label">Color <span id="tpColorName" style="font-weight:700;letter-spacing:0;text-transform:none;">{{ $product->colors->first()?->color_name }}</span></span>
                        <div class="tp-swatches">
                            @foreach ($product->colors as $color)
                                <button type="button" class="tp-swatch {{ $loop->first ? 'active' : '' }}" style="background:{{ $color->hex_code ?? '#ccc' }}" data-color-id="{{ $color->id }}" data-color-name="{{ $color->color_name }}" aria-label="{{ $color->color_name }}"></button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($product->sizes->isNotEmpty())
                    <div class="tp-fieldset">
                        <span class="tp-label">Size</span>
                        <div class="tp-sizes">
                            @foreach ($product->sizes as $size)
                                <button type="button" class="tp-size" data-size-id="{{ $size->id }}" {{ !$size->is_available ? 'disabled' : '' }}>{{ $size->size_label }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('tenant.cart.add', $product->id) }}">
                    @csrf
                    <input type="hidden" name="variant_id" id="tpVariantId">
                    <input type="hidden" name="quantity" id="tpQuantityInput" value="1">
                    <div class="tp-fieldset">
                        <span class="tp-label">Quantity</span>
                        <div class="tp-qty">
                            <button type="button" id="tpQtyMinus">-</button>
                            <span id="tpQtyText">1</span>
                            <button type="button" id="tpQtyPlus">+</button>
                        </div>
                    </div>

                    <div class="tp-actions">
                        @if ($tenant->hasModule('cart'))
                            <button type="submit" class="tp-btn primary"><i class="ti ti-shopping-cart-plus"></i> Add to Cart</button>
                        @endif
                        <div class="tp-actions-row">
                            @if ($tenant->hasModule('checkout'))
                                <a href="{{ route('tenant.checkout') }}" class="tp-btn secondary"><i class="ti ti-credit-card"></i> Checkout</a>
                            @endif
                            @if ($waNumber)
                                <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hello, I want to order ' . $product->name) }}" target="_blank" class="tp-btn secondary" id="tpWhatsappBtn"><i class="ti ti-brand-whatsapp"></i> WhatsApp</a>
                            @endif
                        </div>
                    </div>
                </form>

                @if ($tenant->hasModule('wishlist'))
                    <form method="POST" action="{{ route('tenant.wishlist.toggle', $product->id) }}">
                        @csrf
                        <button type="submit" class="tp-btn secondary" style="width:100%;"><i class="ti ti-heart"></i> Save to Wishlist</button>
                    </form>
                @endif

                <div class="tp-details">
                    <div class="tp-detail"><strong>Product Details</strong><p>{{ $product->material ?: 'Premium product from ' . $tenant->name }}@if($product->weight) · {{ $product->weight }}@endif</p></div>
                    <div class="tp-detail"><strong>Care</strong><p>{{ $product->care_instructions ?: 'Follow store care instructions. Contact the store for product-specific care guidance.' }}</p></div>
                    <div class="tp-detail"><strong>Shipping & Returns</strong><p>Checkout, COD, WhatsApp order, shipping rules, and order tracking are handled by the store where enabled.</p></div>
                </div>
            </article>
        </section>

        @if ($relatedProducts->isNotEmpty())
            <section class="tp-related">
                <div class="tp-section-title">Related Products</div>
                <div class="tp-related-grid">
                    @foreach ($relatedProducts as $related)
                        @php $relatedImage = $related->main_image ? Storage::url($related->main_image) : null; @endphp
                        <a href="{{ route('tenant.product.show', $related->slug) }}" class="tp-related-card">
                            @if ($relatedImage)<img src="{{ $relatedImage }}" alt="{{ $related->name }}" loading="lazy">@endif
                            <div>{{ $related->name }}<br><span style="color:var(--tp-accent, var(--accent-teal));">₹{{ number_format($related->price, 2) }}</span></div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <footer class="tp-foot">{{ $tenant->name }} · Powered by Ehlom OS</footer>
</div>

<script>
    const images = @json($images);
    const track = document.getElementById('tpGalleryTrack');
    const count = document.getElementById('tpGalleryCount');
    const thumbs = [...document.querySelectorAll('.tp-thumb')];
    let index = 0;
    function setImage(next) {
        if (!track || !images.length) return;
        index = (next + images.length) % images.length;
        track.style.transform = 'translateX(-' + (index * 100) + '%)';
        if (count) count.textContent = (index + 1) + ' / ' + images.length;
        thumbs.forEach((thumb, i) => thumb.classList.toggle('active', i === index));
    }
    document.getElementById('tpGalleryPrev')?.addEventListener('click', () => setImage(index - 1));
    document.getElementById('tpGalleryNext')?.addEventListener('click', () => setImage(index + 1));
    thumbs.forEach(thumb => thumb.addEventListener('click', () => setImage(Number(thumb.dataset.index || 0))));

    const variants = @json($variants);
    let selectedColor = document.querySelector('.tp-swatch')?.dataset.colorId || 'base';
    let selectedSize = 'base';
    const variantInput = document.getElementById('tpVariantId');
    function updateVariant() {
        const variant = variants[selectedColor + '_' + selectedSize] || variants[selectedColor + '_base'] || variants['base_' + selectedSize];
        if (variantInput) variantInput.value = variant?.id || '';
    }
    document.querySelectorAll('.tp-swatch').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('.tp-swatch').forEach(item => item.classList.remove('active'));
        button.classList.add('active');
        selectedColor = button.dataset.colorId || 'base';
        document.getElementById('tpColorName').textContent = button.dataset.colorName || '';
        updateVariant();
    }));
    document.querySelectorAll('.tp-size').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('.tp-size').forEach(item => item.classList.remove('active'));
        button.classList.add('active');
        selectedSize = button.dataset.sizeId || 'base';
        updateVariant();
    }));
    let qty = 1;
    const qtyText = document.getElementById('tpQtyText');
    const qtyInput = document.getElementById('tpQuantityInput');
    function setQty(value) {
        qty = Math.max(1, Math.min(99, value));
        qtyText.textContent = qty;
        qtyInput.value = qty;
    }
    document.getElementById('tpQtyMinus')?.addEventListener('click', () => setQty(qty - 1));
    document.getElementById('tpQtyPlus')?.addEventListener('click', () => setQty(qty + 1));
    updateVariant();
</script>
</body>
</html>
