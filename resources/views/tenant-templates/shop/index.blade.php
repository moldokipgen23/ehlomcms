<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $tenant->name }} — Shop</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @if ($tenant->action_type === 'razorpay')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .tp-wrap { min-height: 100vh; display: flex; flex-direction: column; }
        .tp-hero { position: relative; height: 320px; overflow: hidden; display: flex; align-items: flex-end; }
        .tp-hero-bg { position: absolute; inset: 0; object-fit: cover; width: 100%; height: 100%; }
        .tp-hero-overlay { position: absolute; inset: 0; background: linear-gradient(transparent 40%, rgba(18,20,29,.92)); }
        .tp-hero-content { position: relative; z-index: 1; padding: 24px 32px 32px; }
        .tp-name { font-size: 32px; font-weight: 700; color: #fff; font-family: 'Syne', sans-serif; }
        .tp-section { padding: 40px 32px; border-bottom: 1px solid var(--border); }
        .tp-section-title { font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 16px; }
        .tp-about { font-size: 14px; color: var(--text-secondary); line-height: 1.7; max-width: 720px; white-space: pre-wrap; }
        .tp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 14px; }
        .tp-card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 11px; overflow: hidden; }
        .tp-card-img { width: 100%; height: 180px; object-fit: cover; display: block; background: var(--bg-hover); }
        .tp-card-body { padding: 12px 14px 14px; }
        .tp-card-name { font-size: 14px; font-weight: 600; color: var(--text-primary); }
        .tp-card-desc { font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .tp-card-foot { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
        .tp-price { font-size: 16px; font-weight: 700; color: var(--tp-accent, var(--accent-teal)); }
        .tp-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .tp-gallery-img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
        .tp-contact { font-size: 14px; color: var(--text-secondary); line-height: 1.8; }
        .tp-contact a { color: var(--tp-accent, var(--accent-blue)); text-decoration: none; }
        .tp-contact a:hover { text-decoration: underline; }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); }
    </style>
</head>
<body class="antialiased">
    @php
        $ts = $tenant->theme_settings ?? [];
        $accent = $ts['accent_color'] ?? '#4f8ef7';
        $showAbout = $ts['show_about'] ?? true;
        $showGallery = $ts['show_gallery'] ?? true;
        $showContact = $ts['show_contact'] ?? true;
        $cartCount = session('tenant_cart_' . ($tenant->id ?? 'guest'), []);
        $cartCount = array_sum(array_column($cartCount, 'quantity'));
    @endphp
    <div class="tp-wrap" style="--tp-accent: {{ $accent }};">

        {{-- Hero / Banner --}}
        <div class="tp-hero" style="background: linear-gradient(160deg, #1a2240, #0d0f17);">
            @if ($tenant->banner_image)
                <img class="tp-hero-bg" src="{{ Storage::url($tenant->banner_image) }}" alt="">
            @endif
            <div class="tp-hero-overlay"></div>
            <div class="tp-hero-content">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                    @if ($tenant->logo)
                        <img src="{{ Storage::url($tenant->logo) }}" alt="Logo" style="height:44px;border-radius:8px;">
                    @endif
                    <div class="tp-name">{{ $tenant->name }}</div>
                    <div style="margin-left:auto;">
                        <a href="{{ route('tenant.cart') }}" style="color:#fff;text-decoration:none;position:relative;display:inline-flex;align-items:center;gap:4px;font-size:14px;">
                            <i class="ti ti-shopping-cart" style="font-size:22px;"></i>
                            @if ($cartCount > 0)
                                <span style="position:absolute;top:-6px;right:-10px;background:var(--tp-accent, #4f8ef7);color:#fff;font-size:10px;font-weight:700;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Catalog --}}
        <div class="tp-section">
            <div class="tp-section-title">Catalog</div>
            @if ($products->count())
                <div class="tp-grid">
                    @foreach ($products as $product)
                        <div class="tp-card">
                            @if ($product->photo)
                                <img class="tp-card-img" src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}">
                            @endif
                            <div class="tp-card-body">
                                <div class="tp-card-name">{{ $product->name }}</div>
                                @if ($product->description)
                                    <div class="tp-card-desc">{{ $product->description }}</div>
                                @endif
                                <div class="tp-card-foot">
                                    <span class="tp-price">₹{{ number_format($product->price, 2) }}</span>
                                    <div style="display:flex;gap:6px;">
                                        <form action="{{ route('tenant.cart.add', $product) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="eos-btn eos-btn-outline" style="padding:5px 10px;font-size:11px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;">
                                                <i class="ti ti-shopping-cart-plus"></i> Cart
                                            </button>
                                        </form>
                                        <x-tenant-action-button :product="$product" label="Buy Now" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="eos-empty" style="text-align:left;">No products yet.</div>
            @endif
        </div>

        {{-- About --}}
        @if ($showAbout && $tenant->about_text)
            <div class="tp-section">
                <div class="tp-section-title">About</div>
                <div class="tp-about">{{ $tenant->about_text }}</div>
            </div>
        @endif

        {{-- Gallery --}}
        @if ($showGallery && $tenant->galleryImages->count())
            <div class="tp-section">
                <div class="tp-section-title">Gallery</div>
                <div class="tp-gallery-grid">
                    @foreach ($tenant->galleryImages as $image)
                        <img class="tp-gallery-img" src="{{ Storage::url($image->image_path) }}" alt="{{ $image->caption ?? '' }}" loading="lazy">
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Contact --}}
        @if ($showContact)
            <div class="tp-section">
                <div class="tp-section-title">Contact</div>
                <div class="tp-contact">
                    @if ($tenant->contact_address)
                        <div><i class="ti ti-map-pin" style="width:18px;"></i> {{ $tenant->contact_address }}</div>
                    @endif
                    @if ($tenant->contact_phone)
                        <div><i class="ti ti-phone" style="width:18px;"></i> <a href="tel:{{ $tenant->contact_phone }}">{{ $tenant->contact_phone }}</a></div>
                    @endif
                    @if ($tenant->contact_email)
                        <div><i class="ti ti-mail" style="width:18px;"></i> <a href="mailto:{{ $tenant->contact_email }}">{{ $tenant->contact_email }}</a></div>
                    @endif
                    @if ($tenant->contact_hours)
                        <div><i class="ti ti-clock" style="width:18px;"></i> {{ $tenant->contact_hours }}</div>
                    @endif
                </div>
            </div>
        @endif

        <div class="tp-foot">{{ $tenant->name }} &middot; Powered by Ehlom OS</div>
    </div>
</body>
</html>
