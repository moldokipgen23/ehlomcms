<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $tenant->name }} — Info</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

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
        .tp-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .tp-gallery-img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
        .tp-contact { font-size: 14px; color: var(--text-secondary); line-height: 1.8; }
        .tp-contact a { color: var(--accent-blue); text-decoration: none; }
        .tp-contact a:hover { text-decoration: underline; }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); }
    </style>
</head>
<body class="antialiased">
    <div class="tp-wrap">

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
                </div>
            </div>
        </div>

        {{-- About --}}
        @if ($tenant->about_text)
            <div class="tp-section">
                <div class="tp-section-title">About</div>
                <div class="tp-about">{{ $tenant->about_text }}</div>
            </div>
        @endif

        {{-- Gallery --}}
        @if ($tenant->galleryImages->count())
            <div class="tp-section">
                <div class="tp-section-title">Gallery</div>
                <div class="tp-gallery-grid">
                    @foreach ($tenant->galleryImages as $image)
                        <img class="tp-gallery-img" src="{{ Storage::url($image->image_path) }}" alt="{{ $image->caption ?? '' }}" loading="lazy">
                    @endforeach
                </div>
            </div>
        @endif

        {{-- CTA Button --}}
        <div class="tp-section">
            <div class="tp-section-title">Get in Touch</div>
            @if ($tenant->action_type === 'whatsapp' || $tenant->action_type === 'razorpay')
                <x-tenant-action-button label="Contact Us / Donate" />
            @elseif ($tenant->contact_email)
                <a href="mailto:{{ $tenant->contact_email }}" class="eos-btn eos-btn-primary" style="text-decoration:none;">
                    <i class="ti ti-mail"></i> Email Us
                </a>
            @elseif ($tenant->contact_phone)
                <a href="tel:{{ $tenant->contact_phone }}" class="eos-btn eos-btn-primary" style="text-decoration:none;">
                    <i class="ti ti-phone"></i> Call Us
                </a>
            @endif
        </div>

        {{-- Contact --}}
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

        <div class="tp-foot">{{ $tenant->name }} &middot; Powered by Ehlom OS</div>
    </div>
</body>
</html>
