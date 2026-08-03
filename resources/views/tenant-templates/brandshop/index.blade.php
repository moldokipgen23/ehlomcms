<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $tenant->name }} — Official Store</title>
    @if (($tenant->theme_settings['favicon'] ?? null))
        <link rel="icon" href="{{ Storage::url($tenant->theme_settings['favicon']) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @if ($tenant->action_type === 'razorpay')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { background: #f5f1e9; color: #171717; }
        .tp-wrap { min-height: 100vh; display: flex; flex-direction: column; background: #f5f1e9; color: #171717; }
        .tp-store-nav { position: absolute; z-index: 5; top: 16px; left: 0; right: 0; display: flex; align-items: center; justify-content: space-between; gap: 18px; width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 10px 12px; border: 1px solid rgba(255,255,255,.18); border-radius: 999px; background: rgba(11, 13, 18, .64); box-shadow: 0 18px 60px rgba(0,0,0,.22); backdrop-filter: blur(18px); }
        .tp-brand-mark { display: inline-flex; align-items: center; gap: 10px; color: #fff; text-decoration: none; font-weight: 900; letter-spacing: .3px; }
        .tp-brand-logo { width: 42px; height: 42px; border-radius: 12px; object-fit: cover; border: 1px solid rgba(255,255,255,.22); }
        .tp-brand-initial { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; background: var(--tp-accent, #2563eb); color: #fff; font-weight: 900; }
        .tp-store-links { display: flex; align-items: center; gap: 6px; flex-wrap: nowrap; }
        .tp-store-links a { color: rgba(255,255,255,.78); text-decoration: none; font-size: 11px; font-weight: 900; letter-spacing: .5px; text-transform: uppercase; min-height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 0 12px; transition: background .18s ease, color .18s ease, transform .18s ease; }
        .tp-store-links a:hover { color: #fff; }
        .tp-store-links a:hover, .tp-nav-action { background: rgba(255,255,255,.1); }
        .tp-nav-action { width: 38px; padding: 0 !important; position: relative; font-size: 16px !important; letter-spacing: 0 !important; }
        .tp-nav-count { position: absolute; top: -4px; right: -3px; min-width: 17px; height: 17px; display: grid; place-items: center; border-radius: 999px; background: #d7a84f; color: #111; font-size: 10px; font-weight: 900; }
        .tp-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
        .tp-hero { position: relative; min-height: clamp(460px, 72vh, 680px); overflow: hidden; display: flex; align-items: flex-end; }
        .tp-hero-bg { position: absolute; inset: 0; object-fit: cover; width: 100%; height: 100%; }
        .tp-hero-overlay { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(10,15,28,.94) 0%, rgba(10,15,28,.74) 42%, rgba(10,15,28,.18) 100%), linear-gradient(0deg, rgba(10,15,28,.92), transparent 42%); }
        .tp-hero-content { position: relative; z-index: 1; width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 72px 0; }
        .tp-eyebrow { color: var(--tp-accent, #4f8ef7); font-size: 12px; font-weight: 800; letter-spacing: 1.4px; text-transform: uppercase; margin-bottom: 8px; }
        .tp-name { font-size: clamp(42px, 7vw, 84px); line-height: .96; font-weight: 700; color: #fff; font-family: 'Syne', sans-serif; max-width: 820px; }
        .tp-subtitle { color: #dbe4f0; font-size: clamp(15px, 2vw, 19px); line-height: 1.7; margin-top: 16px; max-width: 620px; }
        .tp-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
        .tp-hero-btn { display: inline-flex; align-items: center; gap: 8px; min-height: 46px; padding: 12px 18px; border-radius: 999px; font-size: 13px; font-weight: 800; text-decoration: none; }
        .tp-hero-btn.primary { background: var(--tp-accent, #4f8ef7); color: #fff; }
        .tp-hero-btn.secondary { background: rgba(255,255,255,.12); color: #fff; border: 1px solid rgba(255,255,255,.2); }
        .tp-section { padding: clamp(42px, 7vw, 76px) 32px; border-bottom: 1px solid #e7ded1; }
        .tp-section-inner { width: min(1180px, 100%); margin: 0 auto; }
        .tp-section-head { display: flex; align-items: end; justify-content: space-between; gap: 18px; margin-bottom: 22px; }
        .tp-section-title { font-size: 12px; font-weight: 900; color: #9a642c; text-transform: uppercase; letter-spacing: 1.8px; margin-bottom: 8px; }
        .tp-section-heading { margin: 0; font-family: 'Syne', sans-serif; color: #171717; font-size: clamp(28px, 4vw, 48px); line-height: 1; }
        .tp-section-copy { color: #6f685f; font-size: 14px; line-height: 1.7; max-width: 560px; margin: 0; }
        .tp-about { font-size: 14px; color: var(--text-secondary); line-height: 1.7; max-width: 720px; white-space: pre-wrap; }
        .tp-highlights { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 18px; }
        .tp-highlight { display: inline-flex; align-items: center; gap: 6px; padding: 8px 11px; border: 1px solid var(--border); border-radius: 999px; color: var(--text-secondary); font-size: 12px; font-weight: 700; background: var(--bg-card); }
        .tp-highlight i { color: var(--tp-accent, #4f8ef7); }
        .tp-category-strip { display: flex; gap: 10px; overflow-x: auto; padding: 2px 2px 18px; margin-bottom: 8px; scrollbar-width: thin; }
        .tp-category-pill { white-space: nowrap; display: inline-flex; align-items: center; gap: 8px; min-height: 38px; padding: 0 15px; border-radius: 999px; border: 1px solid #dfd4c6; background: #fffaf2; color: #4b3f33; font-size: 12px; font-weight: 900; text-decoration: none; box-shadow: 0 8px 22px rgba(76,56,35,.06); }
        .tp-category-pill:hover { border-color: #c38948; color: #8a551e; }
        .tp-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
        .tp-card { background: #fffaf5; border: 1px solid #eadfce; border-radius: 20px; overflow: hidden; box-shadow: 0 22px 60px rgba(51,38,24,.08); position: relative; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .tp-card:hover { transform: translateY(-4px); border-color: #d4b68e; box-shadow: 0 30px 80px rgba(51,38,24,.14); }
        .tp-card-media { position: relative; display: block; background: #efe5d8; overflow: hidden; }
        .tp-card-img { width: 100%; aspect-ratio: 4 / 5; object-fit: cover; display: block; transition: transform .35s ease; }
        .tp-card:hover .tp-card-img { transform: scale(1.035); }
        .tp-card-badge { position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; min-height: 26px; padding: 0 9px; border-radius: 999px; background: rgba(255,250,245,.92); color: #8a551e; font-size: 10px; font-weight: 900; letter-spacing: .8px; text-transform: uppercase; box-shadow: 0 8px 22px rgba(0,0,0,.1); }
        .tp-card-wish { position: absolute; top: 10px; right: 10px; width: 34px; height: 34px; border-radius: 999px; border: 0; background: rgba(18,18,18,.66); color: #fff; display: grid; place-items: center; cursor: pointer; backdrop-filter: blur(12px); }
        .tp-card-body { padding: 15px 15px 16px; }
        .tp-card-kicker { font-size: 10px; color: #9a642c; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px; }
        .tp-card-name { font-size: 16px; line-height: 1.25; font-weight: 900; color: #171717; text-decoration: none; display: block; min-height: 40px; }
        .tp-card-name:hover { color: #8a551e; }
        .tp-card-desc { font-size: 12px; color: #756b60; margin-top: 7px; line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 36px; }
        .tp-card-foot { display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 10px; margin-top: 14px; }
        .tp-price { font-size: 18px; font-weight: 900; color: #111; letter-spacing: -.2px; }
        .tp-card-actions { display: flex; gap: 7px; align-items: center; justify-content: flex-end; }
        .tp-shop-btn { min-height: 34px; border-radius: 999px; border: 1px solid #dfd4c6; background: #fff; color: #3a3027; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 900; text-decoration: none; cursor: pointer; white-space: nowrap; }
        .tp-shop-btn.primary { background: #171717; border-color: #171717; color: #fff; }
        .tp-shop-btn.icon { width: 34px; padding: 0; justify-content: center; }
        .tp-shop-btn:hover { border-color: #c38948; color: #8a551e; }
        .tp-shop-btn.primary:hover { background: #8a551e; border-color: #8a551e; color: #fff; }
        .tp-collection-row { display: grid; grid-template-columns: repeat(3, minmax(240px, 1fr)); gap: 16px; overflow-x: auto; padding-bottom: 6px; }
        .tp-collection-card { min-height: 250px; border-radius: 22px; overflow: hidden; border: 1px solid #eadfce; background: #171717; padding: 22px; display: flex; flex-direction: column; justify-content: flex-end; text-decoration: none; position: relative; box-shadow: 0 24px 70px rgba(33,25,16,.12); }
        .tp-collection-card img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .78; transition: transform .35s ease, opacity .35s ease; }
        .tp-collection-card:before { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,.08), rgba(0,0,0,.72)); }
        .tp-collection-card:hover img { transform: scale(1.04); opacity: .9; }
        .tp-collection-card > * { position: relative; z-index: 1; }
        .tp-collection-card strong { color: #fff; font-family: 'Syne', sans-serif; font-size: 26px; line-height: 1; }
        .tp-collection-card span { color: rgba(255,255,255,.78); font-size: 13px; line-height: 1.5; margin-top: 9px; max-width: 320px; }
        .tp-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .tp-gallery-img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
        .tp-contact { font-size: 14px; color: var(--text-secondary); line-height: 1.8; }
        .tp-contact a { color: var(--tp-accent, var(--accent-blue)); text-decoration: none; }
        .tp-contact a:hover { text-decoration: underline; }
        .tp-trust { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; padding: 18px 32px; background: #19140f; border-bottom: 1px solid rgba(255,255,255,.08); }
        .tp-trust-card { display: flex; align-items: center; justify-content: center; gap: 9px; color: rgba(255,255,255,.78); font-size: 12.5px; font-weight: 800; }
        .tp-trust-card i { color: var(--tp-accent, #4f8ef7); font-size: 18px; }
        .tp-social { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 10px; }
        .tp-social a { color: var(--text-muted); text-decoration: none; font-size: 13px; }
        .tp-social a:hover { color: var(--tp-accent, #4f8ef7); }
        .tp-policy-links { display: flex; gap: 10px 14px; justify-content: center; flex-wrap: wrap; margin-top: 12px; }
        .tp-policy-links a { color: var(--text-muted); text-decoration: none; font-size: 12px; font-weight: 700; }
        .tp-policy-links a:hover { color: var(--tp-accent, #4f8ef7); }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); }
        @media (max-width: 640px) {
            .tp-store-nav { top: 12px; padding: 8px; border-radius: 22px; align-items: center; }
            .tp-brand-mark span { max-width: 118px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .tp-store-links { justify-content: flex-end; gap: 4px; }
            .tp-store-links a:not(.tp-nav-action) { display: none; }
            .tp-hero-content { padding-top: 118px; }
            .tp-section { padding-left: 18px; padding-right: 18px; }
            .tp-section-head { display: block; }
            .tp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .tp-card-body { padding: 12px; }
            .tp-card-name { font-size: 13px; min-height: 34px; }
            .tp-card-desc { font-size: 11px; min-height: 34px; }
            .tp-price { font-size: 15px; }
            .tp-card-foot { grid-template-columns: 1fr; align-items: start; }
            .tp-card-actions { justify-content: flex-start; }
            .tp-shop-btn .tp-btn-label { display: none; }
            .tp-shop-btn { width: 34px; padding: 0; justify-content: center; }
            .tp-collection-row { display: flex; }
            .tp-collection-card { min-width: 82%; }
        }
    </style>
</head>
<body class="antialiased">
    @php
        $ts = $tenant->theme_settings ?? [];
        $accent = $ts['accent_color'] ?? '#2563eb';
        $heroTitle = $ts['store_hero_title'] ?? $tenant->name;
        $heroSubtitle = $ts['store_hero_subtitle'] ?? null;
        $primaryCta = $ts['store_primary_cta'] ?? 'Shop Now';
        $secondaryCta = $ts['store_secondary_cta'] ?? 'View Collections';
        $showAbout = $ts['show_about'] ?? true;
        $showGallery = $ts['show_gallery'] ?? true;
        $showContact = $ts['show_contact'] ?? true;
        $hasCart = $tenant->hasModule('cart');
        $hasWishlist = $tenant->hasModule('wishlist');
        $hasCustomerAccounts = $tenant->hasModule('customer_accounts');
        $hasReviews = $tenant->hasModule('reviews');
        $hasPayments = $tenant->hasModule('payments');
        $hasFilters = $tenant->hasModule('search_filters');
        $hasWhatsappDirect = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number ?? '') !== '';
        $hasDirectAction = $tenant->action_type === 'whatsapp' || $hasPayments || ($tenant->action_type === 'razorpay' && $hasWhatsappDirect);
        $cartCount = session('tenant_cart_' . ($tenant->id ?? 'guest'), []);
        $cartCount = array_sum(array_column($cartCount, 'quantity'));
    @endphp
    <div class="tp-wrap" style="--tp-accent: {{ $accent }};">

        {{-- Hero / Banner --}}
        <div class="tp-hero" style="background: linear-gradient(160deg, #1a2240, #0d0f17);">
            <header class="tp-store-nav">
                <a href="{{ route('tenant.home') }}" class="tp-brand-mark">
                    @if ($tenant->logo)
                        <img src="{{ Storage::url($tenant->logo) }}" alt="{{ $tenant->name }}" class="tp-brand-logo">
                    @else
                        <span class="tp-brand-initial">{{ strtoupper(mb_substr($tenant->name, 0, 1)) }}</span>
                    @endif
                    <span>{{ $tenant->name }}</span>
                </a>
                <nav class="tp-store-links">
                    <a href="#catalog">Shop</a>
                    @if (($collections ?? collect())->count())<a href="#collections">Collections</a>@endif
                    @if ($showAbout && $tenant->about_text)<a href="#about">About</a>@endif
                    @if ($hasWishlist)
                        <a href="{{ route('tenant.wishlist') }}" class="tp-nav-action" title="Wishlist" aria-label="Wishlist"><i class="ti ti-heart"></i><span class="tp-sr">Wishlist</span></a>
                    @endif
                    @if ($hasCustomerAccounts)
                        <a href="{{ session('tenant_customer_' . $tenant->id) ? route('tenant.customer.account') : route('tenant.customer.auth') }}" class="tp-nav-action" title="Account" aria-label="Account"><i class="ti ti-user-circle"></i><span class="tp-sr">Account</span></a>
                    @endif
                    @if ($hasCart)
                        <a href="{{ route('tenant.cart') }}" class="tp-nav-action" title="Cart" aria-label="Cart"><i class="ti ti-shopping-bag"></i>@if($cartCount)<span class="tp-nav-count">{{ $cartCount }}</span>@endif<span class="tp-sr">Cart</span></a>
                    @endif
                </nav>
            </header>
            @if ($tenant->banner_image)
                <img class="tp-hero-bg" src="{{ Storage::url($tenant->banner_image) }}" alt="">
            @endif
            <div class="tp-hero-overlay"></div>
            <div class="tp-hero-content">
                <div style="display:flex;align-items:flex-end;gap:24px;margin-bottom:8px;">
                    <div>
                        @if (!empty($ts['store_hero_eyebrow']))
                            <div class="tp-eyebrow">{{ $ts['store_hero_eyebrow'] }}</div>
                        @endif
                        <div class="tp-name">{{ $heroTitle }}</div>
                        @if ($heroSubtitle)
                            <div class="tp-subtitle">{{ $heroSubtitle }}</div>
                        @endif
                        <div class="tp-hero-actions">
                            <a href="#catalog" class="tp-hero-btn primary"><i class="ti ti-shopping-bag"></i> {{ $primaryCta }}</a>
                            @if (($collections ?? collect())->count())
                                <a href="#catalog" class="tp-hero-btn secondary"><i class="ti ti-folders"></i> {{ $secondaryCta }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $trustItems = array_filter([
                ['ti-truck-delivery', $ts['shipping_promise'] ?? null],
                ['ti-refresh', $ts['return_policy'] ?? null],
                ['ti-shield-check', $ts['quality_promise'] ?? null],
                ['ti-brand-whatsapp', $ts['support_promise'] ?? null],
            ], fn ($item) => !empty($item[1]));
        @endphp
        @if ($trustItems)
            <div class="tp-trust">
                @foreach ($trustItems as [$icon, $text])
                    <div class="tp-trust-card"><i class="ti {{ $icon }}"></i> {{ $text }}</div>
                @endforeach
            </div>
        @endif

        @if (($collections ?? collect())->count())
            <div class="tp-section" id="collections">
                <div class="tp-section-inner">
                    <div class="tp-section-head">
                        <div>
                            <div class="tp-section-title">Curated Drops</div>
                            <h2 class="tp-section-heading">{{ $ts['collections_title'] ?? 'Shop by Collection' }}</h2>
                        </div>
                        <p class="tp-section-copy">Browse ready-made edits for work, weekends, gifting, and daily essentials.</p>
                    </div>
                    <div class="tp-collection-row">
                    @foreach ($collections->take(3) as $collection)
                        @php
                            $collectionImage = $collection->cover_image ?: optional($collection->products->first())->main_image;
                        @endphp
                        <a href="{{ route('tenant.home', ['collection' => $collection->slug]) }}#catalog" class="tp-collection-card">
                            @if ($collectionImage)
                                <img src="{{ Storage::url($collectionImage) }}" alt="{{ $collection->name }}" loading="lazy">
                            @endif
                            <strong>{{ $collection->name }}</strong>
                            <span>{{ $collection->description ?: 'Explore curated products selected for this collection.' }}</span>
                        </a>
                    @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Catalog --}}
        <div class="tp-section" id="catalog">
            <div class="tp-section-inner">
            <div class="tp-section-head">
                <div>
                    <div class="tp-section-title">Shop The Edit</div>
                    <h2 class="tp-section-heading">{{ $ts['featured_products_title'] ?? 'Catalog' }}</h2>
                </div>
                <p class="tp-section-copy">A clean, conversion-focused product grid with category browsing, pricing, cart, wishlist, and product pages.</p>
            </div>
            @if (($categories ?? collect())->count())
                <div class="tp-category-strip" aria-label="Product categories">
                    <a href="{{ route('tenant.home') }}#catalog" class="tp-category-pill"><i class="ti ti-layout-grid"></i> All</a>
                    @foreach ($categories as $category)
                        <a href="{{ route('tenant.home', ['category' => $category->slug]) }}#catalog" class="tp-category-pill"><i class="ti ti-category"></i> {{ $category->name }}</a>
                    @endforeach
                </div>
            @endif
            @if ($hasFilters)
                <form method="GET" action="{{ route('tenant.home') }}" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products" style="flex:1;min-width:180px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg-card);color:var(--text-primary);">
                    <select name="category" style="padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg-card);color:var(--text-primary);">
                        <option value="">All categories</option>
                        @foreach (($categories ?? collect()) as $category)
                            <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @if (($collections ?? collect())->count())
                        <select name="collection" style="padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg-card);color:var(--text-primary);">
                            <option value="">All collections</option>
                            @foreach ($collections as $collection)
                                <option value="{{ $collection->slug }}" {{ request('collection') === $collection->slug ? 'selected' : '' }}>{{ $collection->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button type="submit" class="eos-btn eos-btn-primary" style="border:none;"><i class="ti ti-filter"></i> Filter</button>
                </form>
            @endif
            @if ($products->count())
                <div class="tp-grid">
                    @foreach ($products as $product)
                        <div class="tp-card">
                            <a href="{{ route('tenant.product.show', $product->slug) }}" class="tp-card-media">
                                @if ($product->main_image)
                                    <img class="tp-card-img" src="{{ Storage::url($product->main_image) }}" alt="{{ $product->name }}">
                                @else
                                    <div class="tp-card-img" style="display:flex;align-items:center;justify-content:center;color:var(--text-dim);"><i class="ti ti-photo-off"></i></div>
                                @endif
                                <span class="tp-card-badge">{{ $product->is_featured ? 'Featured' : 'New Arrival' }}</span>
                            </a>
                            @if ($hasWishlist)
                                <form action="{{ route('tenant.wishlist.toggle', $product->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="tp-card-wish" title="Save to wishlist" aria-label="Save {{ $product->name }} to wishlist"><i class="ti ti-heart"></i></button>
                                </form>
                            @endif
                            <div class="tp-card-body">
                                @if ($product->productCategory)
                                    <div class="tp-card-kicker">{{ $product->productCategory->name }}</div>
                                @endif
                                <a href="{{ route('tenant.product.show', $product->slug) }}" class="tp-card-name">{{ $product->name }}</a>
                                @if ($product->description)
                                    <div class="tp-card-desc">{{ $product->description }}</div>
                                @endif
                                <div class="tp-card-foot">
                                    <span class="tp-price">₹{{ number_format($product->price) }}</span>
                                    <div class="tp-card-actions">
                                        <a href="{{ route('tenant.product.show', $product->slug) }}" class="tp-shop-btn primary">
                                            <i class="ti ti-eye"></i> <span class="tp-btn-label">View</span>
                                        </a>
                                        @if ($hasCart)
                                        <form action="{{ route('tenant.cart.add', $product) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="tp-shop-btn icon" title="Add to cart" aria-label="Add {{ $product->name }} to cart">
                                                <i class="ti ti-shopping-cart-plus"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @if ($hasDirectAction)
                                        <x-tenant-action-button :product="$product" label="Buy Now" />
                                        @endif
                                    </div>
                                </div>
                                @if ($hasReviews)
                                    @php $approvedReviews = \App\Models\TenantProductReview::where('tenant_id', $tenant->id)->where('tenant_product_id', $product->id)->where('status', 'approved')->get(); @endphp
                                    <div style="margin-top:10px;border-top:1px solid var(--border);padding-top:10px;">
                                        <div class="tp-card-desc" style="-webkit-line-clamp:1;">{{ $approvedReviews->count() }} reviews @if($approvedReviews->count()) · {{ number_format($approvedReviews->avg('rating'), 1) }}/5 @endif</div>
                                        <form method="POST" action="{{ route('tenant.reviews.storefront', $product->id) }}" style="display:grid;gap:6px;margin-top:8px;">
                                            @csrf
                                            <input name="customer_name" placeholder="Name" style="padding:7px;border:1px solid var(--border);border-radius:6px;background:var(--bg-card);color:var(--text-primary);font-size:12px;" required>
                                            <select name="rating" style="padding:7px;border:1px solid var(--border);border-radius:6px;background:var(--bg-card);color:var(--text-primary);font-size:12px;"><option value="5">5 stars</option><option value="4">4 stars</option><option value="3">3 stars</option><option value="2">2 stars</option><option value="1">1 star</option></select>
                                            <input name="comment" placeholder="Quick review" style="padding:7px;border:1px solid var(--border);border-radius:6px;background:var(--bg-card);color:var(--text-primary);font-size:12px;">
                                            <button class="eos-btn eos-btn-outline" style="padding:6px 10px;font-size:11px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;">Submit Review</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="eos-empty" style="text-align:left;">No products yet.</div>
            @endif
            </div>
        </div>

        {{-- About --}}
        @if ($showAbout && $tenant->about_text)
            <div class="tp-section" id="about">
                <div class="tp-section-title">{{ $ts['about_title'] ?? 'About' }}</div>
                <div class="tp-about">{{ $tenant->about_text }}</div>
                @php $highlights = array_filter([$ts['store_highlight_1'] ?? null, $ts['store_highlight_2'] ?? null, $ts['store_highlight_3'] ?? null]); @endphp
                @if ($highlights)
                    <div class="tp-highlights">
                        @foreach ($highlights as $highlight)
                            <span class="tp-highlight"><i class="ti ti-circle-check"></i> {{ $highlight }}</span>
                        @endforeach
                    </div>
                @endif
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

        <div class="tp-foot">
            <div>{{ $ts['footer_tagline'] ?? $tenant->name }}</div>
            @if (!empty($ts['footer_about']))
                <div style="max-width:640px;margin:8px auto 0;line-height:1.6;">{{ $ts['footer_about'] }}</div>
            @endif
            <div class="tp-social">
                @if (!empty($ts['instagram_url']))<a href="{{ $ts['instagram_url'] }}" target="_blank" rel="noopener"><i class="ti ti-brand-instagram"></i> Instagram</a>@endif
                @if (!empty($ts['facebook_url']))<a href="{{ $ts['facebook_url'] }}" target="_blank" rel="noopener"><i class="ti ti-brand-facebook"></i> Facebook</a>@endif
                @if (!empty($ts['youtube_url']))<a href="{{ $ts['youtube_url'] }}" target="_blank" rel="noopener"><i class="ti ti-brand-youtube"></i> YouTube</a>@endif
            </div>
            <div class="tp-policy-links">
                <a href="{{ route('tenant.policy', 'privacy-policy') }}">Privacy Policy</a>
                <a href="{{ route('tenant.policy', 'terms-and-conditions') }}">Terms & Conditions</a>
                <a href="{{ route('tenant.policy', 'refund-policy') }}">Refund Policy</a>
                <a href="{{ route('tenant.policy', 'shipping-policy') }}">Shipping Policy</a>
                @foreach (($customPages ?? collect()) as $customPage)
                    <a href="{{ route('tenant.custom-page.show', $customPage->slug) }}">{{ $customPage->title }}</a>
                @endforeach
            </div>
            <div style="margin-top:10px;">Powered by Ehlom OS</div>
        </div>
    </div>
    @include('tenant-templates.partials.ai-assistant')
</body>
</html>
