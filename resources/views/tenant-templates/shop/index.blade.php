<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $tenant->name }} — Shop</title>
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
        .tp-wrap { min-height: 100vh; display: flex; flex-direction: column; }
        .tp-hero { position: relative; height: 320px; overflow: hidden; display: flex; align-items: flex-end; }
        .tp-hero-bg { position: absolute; inset: 0; object-fit: cover; width: 100%; height: 100%; }
        .tp-hero-overlay { position: absolute; inset: 0; background: linear-gradient(transparent 40%, rgba(18,20,29,.92)); }
        .tp-hero-content { position: relative; z-index: 1; padding: 24px 32px 32px; }
        .tp-eyebrow { color: var(--tp-accent, #4f8ef7); font-size: 12px; font-weight: 800; letter-spacing: 1.4px; text-transform: uppercase; margin-bottom: 8px; }
        .tp-name { font-size: 32px; font-weight: 700; color: #fff; font-family: 'Syne', sans-serif; }
        .tp-subtitle { color: #dbe4f0; font-size: 14px; line-height: 1.6; margin-top: 8px; max-width: 620px; }
        .tp-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
        .tp-hero-btn { display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 9px 14px; border-radius: 9px; font-size: 13px; font-weight: 800; text-decoration: none; }
        .tp-hero-btn.primary { background: var(--tp-accent, #4f8ef7); color: #fff; }
        .tp-hero-btn.secondary { background: rgba(255,255,255,.12); color: #fff; border: 1px solid rgba(255,255,255,.2); }
        .tp-section { padding: 40px 32px; border-bottom: 1px solid var(--border); }
        .tp-section-title { font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 16px; }
        .tp-about { font-size: 14px; color: var(--text-secondary); line-height: 1.7; max-width: 720px; white-space: pre-wrap; }
        .tp-highlights { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 18px; }
        .tp-highlight { display: inline-flex; align-items: center; gap: 6px; padding: 8px 11px; border: 1px solid var(--border); border-radius: 999px; color: var(--text-secondary); font-size: 12px; font-weight: 700; background: var(--bg-card); }
        .tp-highlight i { color: var(--tp-accent, #4f8ef7); }
        .tp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 14px; }
        .tp-card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 11px; overflow: hidden; }
        .tp-card-img { width: 100%; height: 180px; object-fit: cover; display: block; background: var(--bg-hover); }
        .tp-card-body { padding: 12px 14px 14px; }
        .tp-card-name { font-size: 14px; font-weight: 600; color: var(--text-primary); text-decoration: none; }
        .tp-card-name:hover { color: var(--tp-accent, var(--accent-teal)); }
        .tp-card-desc { font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .tp-card-foot { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
        .tp-price { font-size: 16px; font-weight: 700; color: var(--tp-accent, var(--accent-teal)); }
        .tp-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .tp-gallery-img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
        .tp-contact { font-size: 14px; color: var(--text-secondary); line-height: 1.8; }
        .tp-contact a { color: var(--tp-accent, var(--accent-blue)); text-decoration: none; }
        .tp-contact a:hover { text-decoration: underline; }
        .tp-trust { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; padding: 18px 32px; background: rgba(79,142,247,.06); border-bottom: 1px solid var(--border); }
        .tp-trust-card { display: flex; align-items: center; gap: 9px; color: var(--text-secondary); font-size: 12.5px; font-weight: 700; }
        .tp-trust-card i { color: var(--tp-accent, #4f8ef7); font-size: 18px; }
        .tp-social { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 10px; }
        .tp-social a { color: var(--text-muted); text-decoration: none; font-size: 13px; }
        .tp-social a:hover { color: var(--tp-accent, #4f8ef7); }
        .tp-policy-links { display: flex; gap: 10px 14px; justify-content: center; flex-wrap: wrap; margin-top: 12px; }
        .tp-policy-links a { color: var(--text-muted); text-decoration: none; font-size: 12px; font-weight: 700; }
        .tp-policy-links a:hover { color: var(--tp-accent, #4f8ef7); }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); }
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
            @if ($tenant->banner_image)
                <img class="tp-hero-bg" src="{{ Storage::url($tenant->banner_image) }}" alt="">
            @endif
            <div class="tp-hero-overlay"></div>
            <div class="tp-hero-content">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                    @if ($tenant->logo)
                        <img src="{{ Storage::url($tenant->logo) }}" alt="Logo" style="height:44px;border-radius:8px;">
                    @endif
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
                    @if ($hasCart)
                    <div style="margin-left:auto;">
                        <a href="{{ route('tenant.cart') }}" style="color:#fff;text-decoration:none;position:relative;display:inline-flex;align-items:center;gap:4px;font-size:14px;">
                            <i class="ti ti-shopping-cart" style="font-size:22px;"></i>
                            @if ($cartCount > 0)
                                <span style="position:absolute;top:-6px;right:-10px;background:var(--tp-accent, #4f8ef7);color:#fff;font-size:10px;font-weight:700;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </div>
                    @endif
                    @if ($hasWishlist)
                        <a href="{{ route('tenant.wishlist') }}" style="color:#fff;text-decoration:none;font-size:13px;"><i class="ti ti-heart"></i> Wishlist</a>
                    @endif
                    @if ($hasCustomerAccounts)
                        <a href="{{ session('tenant_customer_' . $tenant->id) ? route('tenant.customer.account') : route('tenant.customer.auth') }}" style="color:#fff;text-decoration:none;font-size:13px;"><i class="ti ti-user-circle"></i> Account</a>
                    @endif
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

        {{-- Catalog --}}
        <div class="tp-section" id="catalog">
            <div class="tp-section-title">{{ $ts['featured_products_title'] ?? 'Catalog' }}</div>
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
                            <a href="{{ route('tenant.product.show', $product->slug) }}" style="display:block;text-decoration:none;">
                                @if ($product->main_image)
                                    <img class="tp-card-img" src="{{ Storage::url($product->main_image) }}" alt="{{ $product->name }}">
                                @else
                                    <div class="tp-card-img" style="display:flex;align-items:center;justify-content:center;color:var(--text-dim);"><i class="ti ti-photo-off"></i></div>
                                @endif
                            </a>
                            <div class="tp-card-body">
                                <a href="{{ route('tenant.product.show', $product->slug) }}" class="tp-card-name">{{ $product->name }}</a>
                                @if ($product->productCategory)
                                    <div class="tp-card-desc" style="-webkit-line-clamp:1;">{{ $product->productCategory->name }}</div>
                                @endif
                                @if ($product->description)
                                    <div class="tp-card-desc">{{ $product->description }}</div>
                                @endif
                                <div class="tp-card-foot">
                                    <span class="tp-price">₹{{ number_format($product->price, 2) }}</span>
                                    <div style="display:flex;gap:6px;">
                                        <a href="{{ route('tenant.product.show', $product->slug) }}" class="eos-btn eos-btn-outline" style="padding:5px 10px;font-size:11px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;text-decoration:none;">
                                            <i class="ti ti-eye"></i> View
                                        </a>
                                        @if ($hasCart)
                                        <form action="{{ route('tenant.cart.add', $product) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="eos-btn eos-btn-outline" style="padding:5px 10px;font-size:11px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;">
                                                <i class="ti ti-shopping-cart-plus"></i> Cart
                                            </button>
                                        </form>
                                        @endif
                                        @if ($hasWishlist)
                                            <form action="{{ route('tenant.wishlist.toggle', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="eos-btn eos-btn-outline" style="padding:5px 10px;font-size:11px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;">
                                                    <i class="ti ti-heart"></i>
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

        {{-- About --}}
        @if ($showAbout && $tenant->about_text)
            <div class="tp-section">
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
