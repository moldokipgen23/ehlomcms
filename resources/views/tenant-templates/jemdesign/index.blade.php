<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name }} - Heritage, Reimagined</title>
    <meta name="description" content="{{ $tenant->theme_settings['meta_description'] ?? 'Traditional Kuki-Zo weave motifs reimagined for contemporary wardrobes.' }}">
    <meta name="theme-color" content="#0B0B0C">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/jemdesign-storefront.css') }}">
    <style>
        .jem-empty-card {
            border: 1px solid rgba(201,160,78,.18);
            background: linear-gradient(135deg, rgba(201,160,78,.08), rgba(255,255,255,.03));
            padding: 34px;
            text-align: center;
            color: var(--text-dim);
        }
        .jem-product-actions {
            position: absolute;
            left: 16px;
            right: 16px;
            bottom: 18px;
            z-index: 5;
            display: flex;
            gap: 8px;
            opacity: 0;
            transform: translateY(10px);
            transition: all .35s var(--ease-out);
        }
        .jem-product-card-link {
            position: absolute;
            inset: 0;
            display: block;
            color: inherit;
            z-index: 2;
        }
        .jem-product-card-link:hover { color: inherit; }
        .product-card:hover .jem-product-actions { opacity: 1; transform: translateY(0); }
        .jem-mini-btn {
            min-height: 40px;
            padding: 10px 12px;
            border-radius: 2px;
            background: var(--gold);
            color: var(--black);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            flex: 1;
            text-align: center;
        }
        .jem-mini-btn.secondary {
            background: rgba(11,11,12,.72);
            color: var(--white);
            border: 1px solid rgba(255,255,255,.16);
        }
        .jem-nav-text {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            color: var(--white-dim);
            font-size: 10px;
            letter-spacing: .16em;
            text-transform: uppercase;
            transition: color .25s;
        }
        .jem-nav-text:hover { color: var(--gold); }
        .jem-fallback-products .product-card__info { opacity: 1; transform: translateY(0); }
        .jem-fallback-products .product-card__overlay { opacity: .85; }
        .footer__bottom a { color: var(--gray); margin-left: 18px; }
        @media (max-width: 760px) {
            .jem-product-actions { opacity: 1; transform: none; }
            .hero { min-height: 680px; }
            .hero__content { padding-bottom: 82px; }
            .section { padding: 72px 0; }
            .product-grid--4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 520px) {
            .product-grid--4 { grid-template-columns: 1fr; }
            .collections__grid,
            .story-strip__inner,
            .about-brand__inner { grid-template-columns: 1fr; }
        }
    </style>
</head>
@php
    $ts = $tenant->theme_settings ?? [];
    $brandName = $tenant->name ?: 'Jem Designs & Co.';
    $waNumber = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number ?? '') ?: '918368873736';
    $waLink = 'https://wa.me/' . $waNumber;
    $cartSession = session('tenant_cart_' . ($tenant->id ?? 'guest'), []);
    $cartCount = array_sum(array_column($cartSession, 'quantity'));
    $preloaderEnabled = $tenant->hasModule('jem_preloader') && (($ts['jem_preloader_enabled'] ?? '1') !== '0');
    $heroImage = !empty($ts['jem_hero_image']) ? Storage::url($ts['jem_hero_image']) : ($tenant->banner_image ? Storage::url($tenant->banner_image) : asset('images/jemdesign/Screenshot_20260630-015444.png'));
    $storyImage = !empty($ts['jem_story_image']) ? Storage::url($ts['jem_story_image']) : asset('images/jemdesign/WhatsApp Image 2026-06-30 at 01.58.32.jpeg');
    $founderImage = !empty($ts['jem_founder_image']) ? Storage::url($ts['jem_founder_image']) : asset('images/jemdesign/Screenshot_20260630-015627.png');
    $detailImage = !empty($ts['jem_detail_image']) ? Storage::url($ts['jem_detail_image']) : asset('images/jemdesign/Screenshot_20260630-015615.png');
    $accentImage = !empty($ts['jem_accent_image']) ? Storage::url($ts['jem_accent_image']) : asset('images/jemdesign/Screenshot_20260630-015604.png');
    $fallbackProducts = collect([
        ['name' => 'Heritage Weave Shirt', 'collection' => 'Signature Series', 'price' => 2499, 'image' => asset('images/jemdesign/Screenshot_20260630-015645.png'), 'swatches' => ['#1B3A5C', '#D4C5A9', '#111112']],
        ['name' => 'Heritage Shawl Wrap', 'collection' => 'HerEDIT', 'price' => 3299, 'image' => asset('images/jemdesign/g.jpeg'), 'swatches' => ['#B7410E', '#228B22', '#C9A04E']],
        ['name' => 'Tribal Motif Tote', 'collection' => 'Everyday Craft', 'price' => 1299, 'image' => asset('images/jemdesign/Screenshot_20260630-015502.png'), 'swatches' => ['#E8DCC8', '#1B3A5C', '#800000']],
        ['name' => 'Woven Stripe Midi', 'collection' => 'Blossoms', 'price' => 1899, 'image' => asset('images/jemdesign/Screenshot_20260630-015538.png'), 'swatches' => ['#8B7355', '#2C4A6E', '#E2725B']],
    ]);
@endphp
<body class="{{ $preloaderEnabled ? 'no-scroll' : '' }}">

@if ($preloaderEnabled)
<div id="loader" class="loader">
    <div class="loader__content">
        <svg class="loader__logo" viewBox="0 0 400 180" xmlns="http://www.w3.org/2000/svg">
            <path class="loader__diamond" d="M195 18 L210 38 L195 58 L180 38 Z" fill="none" stroke="#C9A04E" stroke-width="1.5"/>
            <path class="loader__diamond-fill" d="M195 24 L205 38 L195 52 L185 38 Z" fill="#C9A04E" opacity="0"/>
            <path class="loader__wordmark" d="M100 130 C100 130 105 75 130 75 C145 75 140 100 155 100 C170 100 175 70 190 70 C205 70 200 105 215 105 C230 105 250 60 260 75 C270 90 260 130 260 130" fill="none" stroke="#F2EFE9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <text class="loader__subtitle" x="195" y="158" text-anchor="middle" fill="#F2EFE9" font-family="Montserrat, sans-serif" font-size="11" font-weight="400" letter-spacing="4" opacity="0">DESIGNS &amp; CO.</text>
        </svg>
    </div>
    <button class="loader__skip" id="loaderSkip">Skip</button>
</div>
@endif

<nav id="nav" class="nav">
    <div class="nav__inner">
        <a href="{{ route('tenant.home') }}" class="nav__logo" aria-label="{{ $brandName }}">
            <svg class="nav__logo-svg" viewBox="0 0 140 48" xmlns="http://www.w3.org/2000/svg">
                <path d="M38 6 L44 16 L38 26 L32 16 Z" fill="none" stroke="#C9A04E" stroke-width="1" opacity="0.7"/>
                <text x="52" y="36" fill="#F2EFE9" font-family="'Cormorant Garamond', serif" font-size="34" font-weight="600" font-style="italic" letter-spacing="-1">jem</text>
                <text x="53" y="46" fill="#8A857E" font-family="'Montserrat', sans-serif" font-size="6.5" font-weight="500" letter-spacing="3.5">DESIGNS &amp; CO.</text>
            </svg>
        </a>
        <div class="nav__links">
            <a href="#shop" class="nav__link">Shop</a>
            <a href="#collections" class="nav__link">Collections</a>
            <a href="#story" class="nav__link">Our Story</a>
            <a href="#founder" class="nav__link">The Founder</a>
            <a href="#contact" class="nav__link">Contact</a>
        </div>
        <div class="nav__actions">
            @if ($tenant->hasModule('wishlist'))
                <a href="{{ route('tenant.wishlist') }}" class="jem-nav-text">Wishlist</a>
            @endif
            @if ($tenant->hasModule('customer_accounts'))
                <a href="{{ route('tenant.customer.account') }}" class="jem-nav-text">Account</a>
            @endif
            @if ($tenant->hasModule('cart'))
                <a href="{{ route('tenant.cart') }}" class="nav__cart" aria-label="Cart">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <span class="nav__cart-count {{ $cartCount ? 'visible' : '' }}">{{ $cartCount }}</span>
                </a>
            @endif
            <a href="{{ $waLink }}" target="_blank" class="nav__whatsapp" aria-label="WhatsApp">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
            <button class="nav__hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
        </div>
    </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu__backdrop" id="mobileMenuClose"></div>
    <div class="mobile-menu__panel">
        <div class="mobile-menu__header">
            <svg class="mobile-menu__logo" viewBox="0 0 140 48" xmlns="http://www.w3.org/2000/svg">
                <path d="M38 6 L44 16 L38 26 L32 16 Z" fill="none" stroke="#C9A04E" stroke-width="1" opacity="0.7"/>
                <text x="52" y="36" fill="#F2EFE9" font-family="'Cormorant Garamond', serif" font-size="34" font-weight="600" font-style="italic" letter-spacing="-1">jem</text>
                <text x="53" y="46" fill="#8A857E" font-family="'Montserrat', sans-serif" font-size="6.5" font-weight="500" letter-spacing="3.5">DESIGNS &amp; CO.</text>
            </svg>
        </div>
        <a href="{{ route('tenant.home') }}" class="mobile-menu__link">Home</a>
        <a href="#shop" class="mobile-menu__link">Shop All</a>
        <a href="#collections" class="mobile-menu__link">Collections</a>
        <a href="#story" class="mobile-menu__link">Our Story</a>
        <a href="#founder" class="mobile-menu__link">The Founder</a>
        @if ($tenant->hasModule('wishlist'))
            <a href="{{ route('tenant.wishlist') }}" class="mobile-menu__link">Wishlist</a>
        @endif
        @if ($tenant->hasModule('customer_accounts'))
            <a href="{{ route('tenant.customer.account') }}" class="mobile-menu__link">Account</a>
        @endif
        <a href="{{ $waLink }}" target="_blank" class="mobile-menu__whatsapp">Chat on WhatsApp</a>
    </div>
</div>

<main>
    <section class="hero">
        <div class="hero__bg">
            <img src="{{ $heroImage }}" alt="{{ $brandName }} editorial" class="hero__img" loading="eager">
            <div class="hero__overlay"></div>
        </div>
        <div class="hero__content">
            <span class="hero__eyebrow anim-fade-up">{{ $ts['store_hero_eyebrow'] ?? 'Jem Designs & Co.' }}</span>
            <h1 class="hero__title anim-fade-up" style="animation-delay:.15s">{!! nl2br(e($ts['store_hero_title'] ?? 'Where Heritage Meets the Modern Silhouette')) !!}</h1>
            <p class="hero__sub anim-fade-up" style="animation-delay:.3s">{{ $ts['store_hero_subtitle'] ?? 'Traditional Kuki-Zo weave motifs reimagined for contemporary wardrobes.' }}</p>
            <a href="#shop" class="btn btn--gold anim-fade-up" style="animation-delay:.45s">{{ $ts['store_primary_cta'] ?? 'Discover the Collection' }}</a>
        </div>
        <div class="hero__scroll-indicator anim-fade-up" style="animation-delay:.7s"><span>Scroll</span><div class="hero__scroll-line"></div></div>
    </section>

    <section class="section" id="shop">
        <div class="container">
            <div class="section__header">
                <span class="section__eyebrow anim-reveal">Featured Pieces</span>
                <h2 class="section__title anim-reveal">Modern Heritage Essentials</h2>
                <div class="section__divider anim-reveal"></div>
            </div>
            @if ($products->count())
                @if ($tenant->hasModule('search_filters'))
                    <form method="GET" action="{{ route('tenant.home') }}" class="shop-filters" style="margin-bottom:36px;flex-wrap:wrap;">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products" style="min-height:44px;padding:10px 14px;background:var(--bg-input);border:1px solid var(--gray-light);color:var(--white);min-width:240px;">
                        <select name="category" style="min-height:44px;padding:10px 14px;background:var(--bg-input);border:1px solid var(--gray-light);color:var(--white);">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <button class="shop-filter active" type="submit">Filter</button>
                    </form>
                @endif
                <div class="product-grid product-grid--4">
                    @foreach ($products->take(8) as $product)
                        @php
                            $productImage = $product->main_image ? Storage::url($product->main_image) : (($product->images->first()?->image_path ?? null) ? Storage::url($product->images->first()->image_path) : asset('images/jemdesign/Screenshot_20260630-015645.png'));
                        @endphp
                        <article class="product-card">
                            <a href="{{ route('tenant.product.show', $product->slug) }}" class="jem-product-card-link" aria-label="View {{ $product->name }}">
                                <img class="product-card__img" src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy">
                                <div class="product-card__img-mask"></div>
                                <div class="product-card__overlay"></div>
                                <div class="product-card__info">
                                    <span class="product-card__collection">{{ $product->productCategory?->name ?? 'Jem Designs' }}</span>
                                    <h3 class="product-card__name">{{ $product->name }}</h3>
                                    <span class="product-card__price">₹{{ number_format($product->price, 0) }}</span>
                                </div>
                            </a>
                            <div class="jem-product-actions">
                                @if ($tenant->hasModule('cart'))
                                    <form method="POST" action="{{ route('tenant.cart.add', $product) }}" style="flex:1;">
                                        @csrf
                                        <button class="jem-mini-btn" type="submit">Add to Bag</button>
                                    </form>
                                @endif
                                <a href="{{ $waLink }}?text={{ urlencode('Hello, I want to order ' . $product->name) }}" target="_blank" class="jem-mini-btn secondary">WhatsApp</a>
                            </div>
                            <div class="product-card__swatches">
                                @foreach (($product->colors ?? collect())->take(5) as $color)
                                    <div class="product-card__swatch" style="background:{{ $color->hex_code ?? '#C9A04E' }}"></div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="product-grid product-grid--4 jem-fallback-products">
                    @foreach ($fallbackProducts as $product)
                        <article class="product-card">
                            <img class="product-card__img" src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                            <div class="product-card__img-mask"></div>
                            <div class="product-card__overlay"></div>
                            <div class="product-card__info">
                                <span class="product-card__collection">{{ $product['collection'] }}</span>
                                <h3 class="product-card__name">{{ $product['name'] }}</h3>
                                <span class="product-card__price">₹{{ number_format($product['price'], 0) }}</span>
                            </div>
                            <div class="jem-product-actions">
                                <a href="{{ $waLink }}?text={{ urlencode('Hello, I want to know more about ' . $product['name']) }}" target="_blank" class="jem-mini-btn">Enquire</a>
                            </div>
                            <div class="product-card__swatches">
                                @foreach ($product['swatches'] as $swatch)
                                    <div class="product-card__swatch" style="background:{{ $swatch }}"></div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="jem-empty-card" style="margin-top:24px;">Real products from the Jem tenant dashboard will replace these editorial preview cards once products are added.</div>
            @endif
        </div>
    </section>

    <section class="section collections" id="collections">
        <div class="container">
            <div class="section__header">
                <span class="section__eyebrow anim-reveal">Explore</span>
                <h2 class="section__title anim-reveal">Shop by Collection</h2>
                <div class="section__divider anim-reveal"></div>
            </div>
            <div class="collections__grid">
                @forelse ($collections->take(2) as $collection)
                    <a href="{{ route('tenant.home', ['collection' => $collection->slug]) }}" class="collections__panel anim-reveal">
                        <div class="collections__panel-img">
                            <img src="{{ $collection->cover_image ? Storage::url($collection->cover_image) : asset('images/jemdesign/g.jpeg') }}" alt="{{ $collection->name }}" loading="lazy">
                            <div class="collections__panel-overlay"></div>
                        </div>
                        <div class="collections__panel-content">
                            <span class="collections__panel-count">{{ $collection->name }}</span>
                            <h3>{{ $collection->description ?: $collection->name }}</h3>
                            <span class="collections__panel-cta">Explore Collection</span>
                        </div>
                    </a>
                @empty
                    <a href="#shop" class="collections__panel anim-reveal">
                        <div class="collections__panel-img"><img src="{{ asset('images/jemdesign/g.jpeg') }}" alt="Women's Shawls and Stoles" loading="lazy"><div class="collections__panel-overlay"></div></div>
                        <div class="collections__panel-content"><span class="collections__panel-count">HerEDIT &amp; Blossoms</span><h3>Women's Shawls<br>&amp; Stoles</h3><span class="collections__panel-cta">Explore Collection</span></div>
                    </a>
                    <a href="#shop" class="collections__panel anim-reveal">
                        <div class="collections__panel-img"><img src="{{ asset('images/jemdesign/Screenshot_20260630-015645.png') }}" alt="Men's Heritage Shirts" loading="lazy"><div class="collections__panel-overlay"></div></div>
                        <div class="collections__panel-content"><span class="collections__panel-count">Signature Series</span><h3>Men's<br>Heritage Shirts</h3><span class="collections__panel-cta">Explore Collection</span></div>
                    </a>
                @endforelse
            </div>
        </div>
    </section>

    <section class="story-strip" id="story">
        <div class="story-strip__inner">
            <div class="story-strip__text anim-reveal">
                <span class="section__eyebrow">Our Craft</span>
                <h2 class="story-strip__title">{!! nl2br(e($ts['about_title'] ?? 'Woven With Intention')) !!}</h2>
                <p>{{ $tenant->about_text ?: 'Every motif in our collection carries the weight of generations. We translate traditional Kuki-Zo weave patterns into fabrics and silhouettes that belong in the modern wardrobe.' }}</p>
                <a href="#shop" class="btn btn--outline">View Collection</a>
            </div>
            <div class="story-strip__image anim-reveal"><img src="{{ $storyImage }}" alt="Heritage meets modern" loading="lazy"></div>
        </div>
    </section>

    <section class="about-brand" id="founder">
        <div class="about-brand__inner">
            <div class="about-brand__images">
                <div class="about-brand__img-main"><img src="{{ $founderImage }}" alt="Jem Designs founder" loading="lazy"></div>
                <div class="about-brand__img-detail"><img src="{{ $detailImage }}" alt="Heritage textile craft" loading="lazy"></div>
                <div class="about-brand__img-accent"><img src="{{ $accentImage }}" alt="Traditional weave detail" loading="lazy"></div>
                <div class="about-brand__gold-block"></div>
            </div>
            <div class="about-brand__content">
                <div class="about-brand__text">
                    <span class="section__eyebrow">Meet the Maker</span>
                    <h2 class="about-brand__title">The Woman Behind<br>the <em>Weave</em></h2>
                    <div class="about-brand__divider"></div>
                    <blockquote class="about-brand__quote">"Every thread we weave carries the weight of generations."</blockquote>
                    <p class="about-brand__bio">Jem Designs &amp; Co. bridges two worlds: the ancient and the now, the ceremonial and the everyday, the local and the global.</p>
                    <div class="about-brand__stats">
                        <div class="about-brand__stat"><span class="about-brand__stat-number">100%</span><span class="about-brand__stat-label">Handwoven Textiles</span></div>
                        <div class="about-brand__stat"><span class="about-brand__stat-number">50+</span><span class="about-brand__stat-label">Heritage Motifs</span></div>
                        <div class="about-brand__stat"><span class="about-brand__stat-number">1</span><span class="about-brand__stat-label">Northeast India</span></div>
                    </div>
                    <a href="{{ $waLink }}" target="_blank" class="btn btn--gold">Talk to Jem</a>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer" id="contact">
    <div class="footer__inner">
        <div class="footer__top">
            <div class="footer__brand">
                <svg class="footer__logo" viewBox="0 0 140 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M38 6 L44 16 L38 26 L32 16 Z" fill="none" stroke="#C9A04E" stroke-width="1" opacity="0.7"/>
                    <text x="52" y="36" fill="#F2EFE9" font-family="'Cormorant Garamond', serif" font-size="34" font-weight="600" font-style="italic" letter-spacing="-1">jem</text>
                    <text x="53" y="46" fill="#8A857E" font-family="'Montserrat', sans-serif" font-size="6.5" font-weight="500" letter-spacing="3.5">DESIGNS &amp; CO.</text>
                </svg>
                    <p class="footer__tagline">{{ $ts['footer_tagline'] ?? $ts['footer_about'] ?? 'A seamless blend of heritage and modern design. Traditional Kuki-Zo tribal weave motifs reimagined for contemporary wardrobes.' }}</p>
            </div>
            <div class="footer__nav">
                <div class="footer__col"><h4>Shop</h4><a href="#shop">All Products</a><a href="#collections">Collections</a><a href="{{ $waLink }}" target="_blank">Custom Orders</a></div>
                <div class="footer__col"><h4>About</h4><a href="#story">Our Story</a><a href="#founder">The Founder</a><a href="#contact">Contact</a></div>
                <div class="footer__col"><h4>Help</h4><a href="{{ route('tenant.policy', 'privacy-policy') }}">Privacy Policy</a><a href="{{ route('tenant.policy', 'terms-conditions') }}">Terms</a><a href="{{ route('tenant.policy', 'shipping-policy') }}">Shipping</a></div>
            </div>
        </div>
        <div class="footer__bottom">
            <p>&copy; {{ date('Y') }} {{ $brandName }}. All rights reserved. <a href="{{ route('tenant.policy', 'privacy-policy') }}">Privacy Policy</a><a href="{{ route('tenant.policy', 'refund-policy') }}">Refund Policy</a></p>
        </div>
    </div>
</footer>

<a href="{{ $waLink }}" target="_blank" rel="noopener" class="whatsapp-float" aria-label="Chat on WhatsApp">
    <span class="whatsapp-float__pulse"></span>
    <svg class="whatsapp-float__icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

<script>
    const loader = document.getElementById('loader');
    const loaderSkip = document.getElementById('loaderSkip');
    const hideLoader = () => {
        if (loader) loader.classList.add('hidden');
        document.body.classList.remove('no-scroll');
        document.querySelectorAll('.anim-reveal, .section__divider').forEach((el, index) => setTimeout(() => el.classList.add('revealed'), index * 70));
    };
    loaderSkip?.addEventListener('click', hideLoader);
    window.addEventListener('load', () => setTimeout(hideLoader, loader ? 1200 : 50));
    setTimeout(hideLoader, loader ? 2600 : 100);

    const nav = document.getElementById('nav');
    window.addEventListener('scroll', () => nav?.classList.toggle('scrolled', window.scrollY > 60), { passive: true });

    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileClose = document.getElementById('mobileMenuClose');
    const toggleMenu = (open) => {
        hamburger?.classList.toggle('active', open);
        mobileMenu?.classList.toggle('open', open);
        document.body.classList.toggle('no-scroll', open);
    };
    hamburger?.addEventListener('click', () => toggleMenu(!mobileMenu?.classList.contains('open')));
    mobileClose?.addEventListener('click', () => toggleMenu(false));
    mobileMenu?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => toggleMenu(false)));
</script>
</body>
</html>
