<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product->name }} - {{ $tenant->name }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($product->description ?: 'Shop the Jem Designs collection.'), 155) }}">
    <meta name="theme-color" content="#0B0B0C">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/jemdesign-storefront.css') }}">
    <style>
        .product-detail__crumbs {
            padding-top: 118px;
            margin-bottom: 22px;
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--gray);
        }
        .product-detail__crumbs a { color: var(--gray); }
        .product-detail__crumbs a:hover { color: var(--gold); }
        .product-detail__thumbs {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        .jem-gallery {
            position: relative;
            width: 100%;
        }
        .jem-gallery__viewport {
            position: relative;
            overflow: hidden;
            aspect-ratio: 3 / 4;
            background:
                radial-gradient(circle at center, rgba(255,255,255,.045), transparent 58%),
                linear-gradient(135deg, rgba(201,160,78,.08), rgba(255,255,255,.025)),
                var(--black-card);
        }
        .jem-gallery__track {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform .48s var(--ease-luxury);
            will-change: transform;
        }
        .jem-gallery__slide {
            flex: 0 0 100%;
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .jem-gallery__slide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center center;
            display: block;
        }
        .jem-gallery__slide::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(to bottom, rgba(11,11,12,.22) 0%, transparent 18%, transparent 82%, rgba(11,11,12,.26) 100%);
        }
        .jem-gallery__nav {
            position: absolute;
            top: 50%;
            z-index: 4;
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,.18);
            background: rgba(11,11,12,.62);
            color: var(--white);
            transform: translateY(-50%);
            transition: background .25s, border-color .25s, color .25s;
            backdrop-filter: blur(12px);
        }
        .jem-gallery__nav:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: rgba(11,11,12,.82);
        }
        .jem-gallery__nav--prev { left: 16px; }
        .jem-gallery__nav--next { right: 16px; }
        .jem-gallery__counter {
            position: absolute;
            right: 16px;
            bottom: 16px;
            z-index: 4;
            padding: 8px 11px;
            border: 1px solid rgba(255,255,255,.14);
            background: rgba(11,11,12,.7);
            color: var(--white-dim);
            font-size: 10px;
            letter-spacing: .16em;
            text-transform: uppercase;
            backdrop-filter: blur(12px);
        }
        .jem-gallery__thumbs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 9px;
            margin-top: 12px;
        }
        .product-detail__thumb {
            width: 100%;
            height: 92px;
            border-radius: 2px;
            overflow: hidden;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,.12);
            background: var(--black-card);
            transition: border-color .25s, transform .25s;
        }
        .product-detail__thumb:hover,
        .product-detail__thumb.active {
            border-color: var(--gold);
            transform: translateY(-1px);
        }
        .product-detail__thumb img { width: 100%; height: 100%; object-fit: cover; object-position: center 25%; }
        .product-detail__videos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .product-detail__video-wrap {
            border-radius: 2px;
            overflow: hidden;
            aspect-ratio: 9 / 16;
            background: var(--black-card);
        }
        .product-detail__video-wrap video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .size-btn[disabled] {
            opacity: .35;
            cursor: not-allowed;
        }
        .jem-detail-help {
            margin-top: 14px;
            padding: 16px;
            border: 1px solid rgba(201,160,78,.18);
            background: linear-gradient(135deg, rgba(201,160,78,.08), rgba(255,255,255,.03));
            color: var(--white-dim);
            font-size: 12px;
            line-height: 1.7;
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
        .jem-related-link {
            position: absolute;
            inset: 0;
            z-index: 2;
            color: inherit;
        }
        .footer__bottom a { color: var(--gray); margin-left: 18px; }
        @media (max-width: 1024px) {
            .product-detail__inner {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            .product-detail__gallery {
                position: static;
                width: 100%;
                max-width: 760px;
                margin: 0 auto;
            }
            .product-detail__main-image,
            .jem-gallery__viewport {
                aspect-ratio: 3 / 4;
                max-height: none;
            }
        }
        @media (max-width: 760px) {
            .product-detail__crumbs { padding-top: 96px; }
            .product-grid--4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .jem-gallery__viewport {
                aspect-ratio: 3 / 4;
                min-height: min(86vh, 720px);
            }
            .jem-gallery__nav { width: 40px; height: 40px; }
            .jem-gallery__nav--prev { left: 10px; }
            .jem-gallery__nav--next { right: 10px; }
            .jem-gallery__counter { right: 10px; bottom: 10px; }
        }
        @media (max-width: 520px) {
            .product-grid--4 { grid-template-columns: 1fr; }
            .product-detail__gallery { max-width: none; }
            .jem-gallery__viewport {
                aspect-ratio: 3 / 4;
                min-height: auto;
            }
            .jem-gallery__thumbs {
                display: flex;
                gap: 8px;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                padding-bottom: 4px;
            }
            .product-detail__thumb {
                flex: 0 0 62px;
                width: 62px;
                height: 78px;
                scroll-snap-align: start;
            }
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
    $productImages = collect([$product->main_image])
        ->merge($product->images->pluck('image_path'))
        ->merge($product->colors->flatMap(fn ($color) => $color->images->pluck('image_path')))
        ->filter()
        ->unique()
        ->values();
    $galleryImages = $productImages->map(fn ($path) => Storage::url($path))->values();
    if ($galleryImages->isEmpty()) {
        $galleryImages = collect([asset('images/jemdesign/Screenshot_20260630-015645.png')]);
    }
    $mainImage = $galleryImages->first();
    $colors = $product->colors->map(fn ($color) => [
        'id' => $color->id,
        'name' => $color->color_name,
        'hex' => $color->hex_code ?: '#C9A04E',
        'images' => $color->images->pluck('image_path')->filter()->map(fn ($path) => Storage::url($path))->values()->all(),
    ])->values();
    $variants = $product->variants->where('is_active', true)->mapWithKeys(fn ($variant) => [
        ($variant->tenant_product_color_id ?: 'base') . '_' . ($variant->tenant_product_size_id ?: 'base') => [
            'id' => $variant->id,
            'stock' => $variant->stock,
            'price' => (float) $variant->effective_price,
        ],
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

<nav id="nav" class="nav scrolled">
    <div class="nav__inner">
        <a href="{{ route('tenant.home') }}" class="nav__logo" aria-label="{{ $brandName }}">
            <svg class="nav__logo-svg" viewBox="0 0 140 48" xmlns="http://www.w3.org/2000/svg">
                <path d="M38 6 L44 16 L38 26 L32 16 Z" fill="none" stroke="#C9A04E" stroke-width="1" opacity="0.7"/>
                <text x="52" y="36" fill="#F2EFE9" font-family="'Cormorant Garamond', serif" font-size="34" font-weight="600" font-style="italic" letter-spacing="-1">jem</text>
                <text x="53" y="46" fill="#8A857E" font-family="'Montserrat', sans-serif" font-size="6.5" font-weight="500" letter-spacing="3.5">DESIGNS &amp; CO.</text>
            </svg>
        </a>
        <div class="nav__links">
            <a href="{{ route('tenant.home') }}#shop" class="nav__link">Shop</a>
            <a href="{{ route('tenant.home') }}#collections" class="nav__link">Collections</a>
            <a href="{{ route('tenant.home') }}#story" class="nav__link">Our Story</a>
            <a href="{{ route('tenant.home') }}#founder" class="nav__link">The Founder</a>
            <a href="{{ route('tenant.home') }}#contact" class="nav__link">Contact</a>
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
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
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
        <a href="{{ route('tenant.home') }}#shop" class="mobile-menu__link">Shop All</a>
        <a href="{{ route('tenant.home') }}#collections" class="mobile-menu__link">Collections</a>
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
    <section class="product-detail">
        <div class="container">
            <div class="product-detail__crumbs">
                <a href="{{ route('tenant.home') }}">Home</a>
                <span style="margin:0 8px;opacity:.45">/</span>
                <a href="{{ route('tenant.home') }}#shop">Shop</a>
                <span style="margin:0 8px;opacity:.45">/</span>
                <span>{{ $product->name }}</span>
            </div>

            <div class="product-detail__inner">
                <div class="product-detail__gallery">
                    <div class="jem-gallery" id="jemProductGallery" aria-label="{{ $product->name }} image gallery">
                        <div class="product-detail__main-image jem-gallery__viewport">
                            <div class="jem-gallery__track" id="jemGalleryTrack">
                                @foreach ($galleryImages as $imageUrl)
                                    <figure class="jem-gallery__slide">
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }} view {{ $loop->iteration }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                                    </figure>
                                @endforeach
                            </div>
                            @if ($galleryImages->count() > 1)
                                <button class="jem-gallery__nav jem-gallery__nav--prev" type="button" id="jemGalleryPrev" aria-label="Previous product image">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 18l-6-6 6-6"/></svg>
                                </button>
                                <button class="jem-gallery__nav jem-gallery__nav--next" type="button" id="jemGalleryNext" aria-label="Next product image">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 6l6 6-6 6"/></svg>
                                </button>
                                <div class="jem-gallery__counter" id="jemGalleryCounter">1 / {{ $galleryImages->count() }}</div>
                            @endif
                        </div>
                        @if ($galleryImages->count() > 1)
                            <div class="jem-gallery__thumbs" id="productThumbs">
                                @foreach ($galleryImages as $imageUrl)
                                    <button class="product-detail__thumb {{ $loop->first ? 'active' : '' }}" type="button" data-index="{{ $loop->index }}" aria-label="View image {{ $loop->iteration }}">
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }} thumbnail {{ $loop->iteration }}" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @if ($productImages->count() > 1)
                        <noscript>
                            <div class="product-detail__thumbs">
                                @foreach ($galleryImages as $imageUrl)
                                    <img class="product-detail__thumb" src="{{ $imageUrl }}" alt="{{ $product->name }} view {{ $loop->iteration }}">
                                @endforeach
                            </div>
                        </noscript>
                    @endif
                    @if ($product->videos->isNotEmpty())
                        <div class="product-detail__videos">
                            @foreach ($product->videos as $video)
                                <div class="product-detail__video-wrap">
                                    <video src="{{ Storage::url($video->video_path) }}" playsinline muted loop controls preload="metadata"></video>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="product-detail__info">
                    <span class="product-detail__collection">{{ $product->productCategory?->name ?? $product->collections->first()?->name ?? 'Jem Designs' }}</span>
                    <h1 class="product-detail__name">{{ $product->name }}</h1>
                    <p class="product-detail__price">₹{{ number_format($product->price, 0) }}</p>
                    <p class="product-detail__desc">{{ $product->description ?: 'A signature Jem Designs piece shaped by heritage craft and modern styling.' }}</p>

                    @if ($product->colors->isNotEmpty())
                        <div class="product-detail__swatches">
                            <span class="product-detail__label">Color</span>
                            <div class="swatch-group">
                                @foreach ($product->colors as $color)
                                    <button class="swatch {{ $loop->first ? 'active' : '' }}" type="button" style="background:{{ $color->hex_code ?? '#C9A04E' }}" title="{{ $color->color_name }}" data-color-id="{{ $color->id }}" data-color-name="{{ $color->color_name }}"></button>
                                @endforeach
                            </div>
                            <span class="product-detail__color-name" id="pdColorName">{{ $product->colors->first()?->color_name }}</span>
                        </div>
                    @endif

                    @if ($product->sizes->isNotEmpty())
                        <div class="product-detail__sizes">
                            <span class="product-detail__label">Size</span>
                            <div class="size-group">
                                @foreach ($product->sizes as $size)
                                    <button class="size-btn" type="button" data-size-id="{{ $size->id }}" {{ !$size->is_available ? 'disabled' : '' }}>{{ $size->size_label }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tenant.cart.add', $product->id) }}" class="product-detail__form">
                        @csrf
                        <input type="hidden" name="variant_id" id="variantId">
                        <input type="hidden" name="quantity" id="quantityInput" value="1">
                        <div class="product-detail__qty">
                            <span class="product-detail__label">Quantity</span>
                            <div class="qty-selector">
                                <button class="qty-btn" type="button" id="pdQtyMinus" aria-label="Decrease quantity">-</button>
                                <span class="qty-value" id="pdQty">1</span>
                                <button class="qty-btn" type="button" id="pdQtyPlus" aria-label="Increase quantity">+</button>
                            </div>
                        </div>
                        <div class="product-detail__actions">
                            @if ($tenant->hasModule('cart'))
                                <button class="btn btn--gold btn--full" type="submit" id="pdAddToCart">Add to Bag</button>
                            @endif
                            <a class="btn btn--outline btn--full" id="pdBuyNow" href="{{ $waLink }}?text={{ urlencode('Hello, I want to order ' . $product->name) }}" target="_blank">Order via WhatsApp</a>
                        </div>
                    </form>
                    @if ($tenant->hasModule('wishlist'))
                        <form method="POST" action="{{ route('tenant.wishlist.toggle', $product->id) }}" style="margin-top:-24px;margin-bottom:28px;">
                            @csrf
                            <button type="submit" class="btn btn--outline btn--full">Save to Wishlist</button>
                        </form>
                    @endif

                    <div class="jem-detail-help">Need help with size, color, or custom styling? Send this product to Jem Designs on WhatsApp and confirm your order directly.</div>

                    <div class="product-detail__accordion">
                        <div class="accordion-item open">
                            <button class="accordion-header" type="button"><span>Fabric &amp; Care</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
                            <div class="accordion-body">
                                <p>
                                    @if ($product->material)<strong>Material:</strong> {{ $product->material }}<br>@endif
                                    @if ($product->weight)<strong>Weight:</strong> {{ $product->weight }}<br>@endif
                                    {{ $product->care_instructions ?: 'Dry clean recommended. Store in a cool, dry place away from direct sunlight.' }}
                                </p>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <button class="accordion-header" type="button"><span>Heritage Note</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
                            <div class="accordion-body"><p>{{ $product->heritage_note ?: 'Inspired by traditional Kuki-Zo weaving language, reimagined as a contemporary wardrobe piece.' }}</p></div>
                        </div>
                        <div class="accordion-item">
                            <button class="accordion-header" type="button"><span>Shipping &amp; Returns</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
                            <div class="accordion-body"><p>Orders are confirmed through checkout or WhatsApp. Dispatch timelines, COD, and return support are handled by the store team.</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="section" style="padding-top:0;">
            <div class="container">
                <div class="section__header">
                    <span class="section__eyebrow">Continue Shopping</span>
                    <h2 class="section__title">You May Also Like</h2>
                    <div class="section__divider revealed"></div>
                </div>
                <div class="product-grid product-grid--4">
                    @foreach ($relatedProducts as $related)
                        @php $relatedImage = $related->main_image ? Storage::url($related->main_image) : asset('images/jemdesign/Screenshot_20260630-015645.png'); @endphp
                        <article class="product-card">
                            <a href="{{ route('tenant.product.show', $related->slug) }}" class="jem-related-link" aria-label="View {{ $related->name }}"></a>
                            <img class="product-card__img" src="{{ $relatedImage }}" alt="{{ $related->name }}" loading="lazy">
                            <div class="product-card__img-mask"></div>
                            <div class="product-card__overlay"></div>
                            <div class="product-card__info">
                                <span class="product-card__collection">{{ $related->productCategory?->name ?? 'Jem Designs' }}</span>
                                <h3 class="product-card__name">{{ $related->name }}</h3>
                                <span class="product-card__price">₹{{ number_format($related->price, 0) }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>

<footer class="footer" id="contact">
    <div class="footer__inner">
        <div class="footer__bottom">
            <p>&copy; {{ date('Y') }} {{ $brandName }}. All rights reserved. <a href="{{ route('tenant.policy', 'privacy-policy') }}">Privacy Policy</a><a href="{{ route('tenant.policy', 'refund-policy') }}">Refund Policy</a></p>
        </div>
    </div>
</footer>

<a href="{{ $waLink }}?text={{ urlencode('Hello, I want to know more about ' . $product->name) }}" target="_blank" rel="noopener" class="whatsapp-float" aria-label="Chat on WhatsApp">
    <span class="whatsapp-float__pulse"></span>
    <svg class="whatsapp-float__icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
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
    window.addEventListener('load', () => setTimeout(hideLoader, loader ? 900 : 50));
    setTimeout(hideLoader, loader ? 2200 : 100);

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

    const galleryTrack = document.getElementById('jemGalleryTrack');
    const gallery = document.getElementById('jemProductGallery');
    const galleryThumbs = [...document.querySelectorAll('.product-detail__thumb[data-index]')];
    const galleryCounter = document.getElementById('jemGalleryCounter');
    const galleryImages = @json($galleryImages->values());
    const colorImageMap = @json($colors->mapWithKeys(fn ($color) => [$color['id'] => $color['images'][0] ?? null]));
    let galleryIndex = 0;

    function setGalleryIndex(index) {
        if (!galleryTrack || galleryImages.length < 1) return;
        galleryIndex = (index + galleryImages.length) % galleryImages.length;
        galleryTrack.style.transform = 'translateX(-' + (galleryIndex * 100) + '%)';
        galleryThumbs.forEach((thumb, i) => {
            const active = i === galleryIndex;
            thumb.classList.toggle('active', active);
            if (active) thumb.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
        });
        if (galleryCounter) galleryCounter.textContent = (galleryIndex + 1) + ' / ' + galleryImages.length;
    }

    galleryThumbs.forEach(thumb => thumb.addEventListener('click', () => setGalleryIndex(Number(thumb.dataset.index || 0))));
    document.getElementById('jemGalleryPrev')?.addEventListener('click', () => setGalleryIndex(galleryIndex - 1));
    document.getElementById('jemGalleryNext')?.addEventListener('click', () => setGalleryIndex(galleryIndex + 1));

    let touchStartX = null;
    gallery?.addEventListener('touchstart', event => {
        touchStartX = event.touches?.[0]?.clientX ?? null;
    }, { passive: true });
    gallery?.addEventListener('touchend', event => {
        if (touchStartX === null) return;
        const endX = event.changedTouches?.[0]?.clientX ?? touchStartX;
        const delta = endX - touchStartX;
        if (Math.abs(delta) > 40) setGalleryIndex(galleryIndex + (delta < 0 ? 1 : -1));
        touchStartX = null;
    }, { passive: true });

    document.addEventListener('keydown', event => {
        if (!gallery || !gallery.matches(':hover')) return;
        if (event.key === 'ArrowLeft') setGalleryIndex(galleryIndex - 1);
        if (event.key === 'ArrowRight') setGalleryIndex(galleryIndex + 1);
    });

    const variants = @json($variants);
    let selectedColorId = document.querySelector('.swatch')?.dataset.colorId || 'base';
    let selectedSizeId = 'base';
    const variantInput = document.getElementById('variantId');
    const whatsappBtn = document.getElementById('pdBuyNow');
    const baseWhatsapp = @json($waLink);
    const productName = @json($product->name);

    function updateVariant() {
        const variant = variants[selectedColorId + '_' + selectedSizeId] || variants[selectedColorId + '_base'] || variants['base_' + selectedSizeId];
        variantInput.value = variant?.id || '';
    }

    function updateWhatsapp() {
        const colorName = document.getElementById('pdColorName')?.textContent?.trim();
        const sizeLabel = document.querySelector('.size-btn.active')?.textContent?.trim();
        const qty = document.getElementById('quantityInput')?.value || '1';
        const lines = ['Hello, I want to order ' + productName, 'Quantity: ' + qty];
        if (colorName) lines.push('Color: ' + colorName);
        if (sizeLabel) lines.push('Size: ' + sizeLabel);
        whatsappBtn.href = baseWhatsapp + '?text=' + encodeURIComponent(lines.join('\n'));
    }

    document.querySelectorAll('.swatch').forEach(swatch => {
        swatch.addEventListener('click', () => {
            document.querySelectorAll('.swatch').forEach(item => item.classList.remove('active'));
            swatch.classList.add('active');
            selectedColorId = swatch.dataset.colorId || 'base';
            document.getElementById('pdColorName').textContent = swatch.dataset.colorName || '';
            const colorImage = colorImageMap[selectedColorId];
            const imageIndex = colorImage ? galleryImages.indexOf(colorImage) : -1;
            if (imageIndex >= 0) setGalleryIndex(imageIndex);
            updateVariant();
            updateWhatsapp();
        });
    });

    document.querySelectorAll('.size-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.size-btn').forEach(item => item.classList.remove('active'));
            button.classList.add('active');
            selectedSizeId = button.dataset.sizeId || 'base';
            updateVariant();
            updateWhatsapp();
        });
    });

    let qty = 1;
    const qtyText = document.getElementById('pdQty');
    const qtyInput = document.getElementById('quantityInput');
    const setQty = (value) => {
        qty = Math.max(1, Math.min(99, value));
        qtyText.textContent = qty;
        qtyInput.value = qty;
        updateWhatsapp();
    };
    document.getElementById('pdQtyMinus')?.addEventListener('click', () => setQty(qty - 1));
    document.getElementById('pdQtyPlus')?.addEventListener('click', () => setQty(qty + 1));
    document.querySelectorAll('.accordion-header').forEach(header => header.addEventListener('click', () => header.closest('.accordion-item')?.classList.toggle('open')));
    updateVariant();
    updateWhatsapp();
</script>
</body>
</html>
