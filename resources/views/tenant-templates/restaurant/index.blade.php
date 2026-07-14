<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $tenant->name }} — Menu</title>

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
        .tp-menu-cat { font-size: 18px; font-weight: 600; color: var(--text-primary); font-family: 'Syne', sans-serif; margin: 24px 0 12px; }
        .tp-menu-cat:first-child { margin-top: 0; }
        .tp-menu-item { display: flex; gap: 14px; padding: 12px 0; border-bottom: 1px dashed var(--border); align-items: center; }
        .tp-menu-item:last-child { border-bottom: none; }
        .tp-menu-photo { width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); flex: none; }
        .tp-menu-info { flex: 1; min-width: 0; }
        .tp-menu-item-name { font-size: 15px; font-weight: 600; color: var(--text-primary); }
        .tp-menu-item-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-top: 2px; }
        .tp-menu-right { text-align: right; flex: none; display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
        .tp-price { font-size: 15px; font-weight: 700; color: var(--tp-accent, var(--accent-blue)); }
        .tp-about { font-size: 14px; color: var(--text-secondary); line-height: 1.7; max-width: 720px; white-space: pre-wrap; }
        .tp-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .tp-gallery-img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
        .tp-contact { font-size: 14px; color: var(--text-secondary); line-height: 1.8; }
        .tp-contact a { color: var(--tp-accent, var(--accent-blue)); text-decoration: none; }
        .tp-contact a:hover { text-decoration: underline; }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); }
        .tp-resv-form { max-width: 520px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .tp-resv-form .full { grid-column: 1 / -1; }
        .tp-resv-label { font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 4px; display: block; }
        .tp-resv-input { width: 100%; padding: 9px 11px; border-radius: 7px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-primary); font-size: 14px; }
        .tp-resv-ok { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.4); color: var(--accent-green); padding: 12px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; max-width: 520px; }
        .tp-resv-err { color: var(--accent-red); font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body class="antialiased">
    @php
        $ts = $tenant->theme_settings ?? [];
        $accent = $ts['accent_color'] ?? '#e0653a';
        $showMenu = $ts['show_menu'] ?? true;
        $showReservations = $ts['show_reservations'] ?? true;
        $showAbout = $ts['show_about'] ?? true;
        $showGallery = $ts['show_gallery'] ?? true;
        $showContact = $ts['show_contact'] ?? true;
        $grouped = $products->groupBy(fn ($p) => $p->category ?: 'Menu');
    @endphp
    <div class="tp-wrap" style="--tp-accent: {{ $accent }};">

        {{-- Hero / Banner --}}
        <div class="tp-hero" style="background: linear-gradient(160deg, #2a1a14, #0d0f17);">
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
                </div>
            </div>
        </div>

        {{-- Menu --}}
        @if ($showMenu)
            <div class="tp-section">
                <div class="tp-section-title">Menu</div>
                @if ($products->count())
                    @foreach ($grouped as $category => $items)
                        <div class="tp-menu-cat">{{ $category }}</div>
                        @foreach ($items as $product)
                            <div class="tp-menu-item">
                                @if ($product->photo)
                                    <img class="tp-menu-photo" src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}">
                                @endif
                                <div class="tp-menu-info">
                                    <div class="tp-menu-item-name">{{ $product->name }}</div>
                                    @if ($product->description)
                                        <div class="tp-menu-item-desc">{{ $product->description }}</div>
                                    @endif
                                </div>
                                <div class="tp-menu-right">
                                    <span class="tp-price">₹{{ number_format($product->price, 2) }}</span>
                                    @if ($tenant->action_type === 'whatsapp' || $tenant->action_type === 'razorpay')
                                        <x-tenant-action-button :product="$product" label="Order" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                @else
                    <div class="eos-empty" style="text-align:left;">Menu coming soon.</div>
                @endif
            </div>
        @endif

        {{-- Reservations --}}
        @if ($showReservations)
            <div class="tp-section">
                <div class="tp-section-title">Book a Table</div>

                @if (session('reservation_success'))
                    <div class="tp-resv-ok">{{ session('reservation_success') }}</div>
                @endif

                <form action="{{ route('tenant.reserve') }}" method="POST" class="tp-resv-form">
                    @csrf
                    <div>
                        <label class="tp-resv-label">Your Name *</label>
                        <input type="text" name="customer_name" class="tp-resv-input" value="{{ old('customer_name') }}" required>
                        @error('customer_name') <div class="tp-resv-err">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="tp-resv-label">Phone *</label>
                        <input type="text" name="phone" class="tp-resv-input" value="{{ old('phone') }}" required>
                        @error('phone') <div class="tp-resv-err">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="tp-resv-label">Party Size *</label>
                        <input type="number" name="party_size" class="tp-resv-input" min="1" max="100" value="{{ old('party_size', 2) }}" required>
                        @error('party_size') <div class="tp-resv-err">{{ $message }}</div> @enderror
                    </div>
                    <div></div>
                    <div>
                        <label class="tp-resv-label">Date *</label>
                        <input type="date" name="date" class="tp-resv-input" value="{{ old('date') }}" required>
                        @error('date') <div class="tp-resv-err">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="tp-resv-label">Time *</label>
                        <input type="time" name="time" class="tp-resv-input" value="{{ old('time') }}" required>
                        @error('time') <div class="tp-resv-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="full">
                        <label class="tp-resv-label">Notes (optional)</label>
                        <input type="text" name="notes" class="tp-resv-input" value="{{ old('notes') }}" placeholder="Any special requests?">
                        @error('notes') <div class="tp-resv-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="full">
                        <button type="submit" class="eos-btn eos-btn-primary" style="background:{{ $accent }};border:none;">
                            <i class="ti ti-calendar-plus"></i> Request Reservation
                        </button>
                    </div>
                </form>
            </div>
        @endif

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
    @include('tenant-templates.partials.ai-assistant')
</body>
</html>
