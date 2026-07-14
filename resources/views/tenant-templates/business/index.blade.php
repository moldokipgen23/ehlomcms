<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $tenant->name }}</title>

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
        .tp-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .tp-gallery-img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
        .tp-contact { font-size: 14px; color: var(--text-secondary); line-height: 1.8; }
        .tp-contact a { color: var(--tp-accent, var(--accent-blue)); text-decoration: none; }
        .tp-contact a:hover { text-decoration: underline; }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); }

        .tp-svc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
        .tp-svc-card { border: 1px solid var(--border); border-radius: 10px; padding: 16px; background: var(--bg-card); }
        .tp-svc-photo { width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
        .tp-svc-name { font-size: 15px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px; }
        .tp-svc-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 8px; }
        .tp-svc-price { font-size: 14px; font-weight: 700; color: var(--tp-accent, var(--accent-blue)); }

        .tp-test-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
        .tp-test-card { border: 1px solid var(--border); border-radius: 10px; padding: 16px; background: var(--bg-card); }
        .tp-test-quote { font-size: 13px; color: var(--text-secondary); line-height: 1.6; font-style: italic; margin-bottom: 12px; }
        .tp-test-author { display: flex; align-items: center; gap: 10px; }
        .tp-test-photo { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
        .tp-test-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
        .tp-test-role { font-size: 11px; color: var(--text-dim); }
        .tp-test-stars { color: var(--accent-amber); font-size: 12px; margin-bottom: 6px; }

        .tp-blog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
        .tp-blog-card { border: 1px solid var(--border); border-radius: 10px; overflow: hidden; background: var(--bg-card); }
        .tp-blog-photo { width: 100%; height: 130px; object-fit: cover; }
        .tp-blog-body { padding: 14px; }
        .tp-blog-title { font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px; }
        .tp-blog-excerpt { font-size: 12px; color: var(--text-secondary); line-height: 1.5; }
        .tp-blog-date { font-size: 11px; color: var(--text-dim); margin-top: 8px; }
    </style>
</head>
<body class="antialiased">
    @php
        $ts = $tenant->theme_settings ?? [];
        $accent = $ts['accent_color'] ?? '#534ab7';
        $showServices = $ts['show_services'] ?? true;
        $showTestimonials = $ts['show_testimonials'] ?? true;
        $showBlog = $ts['show_blog'] ?? true;
        $showAbout = $ts['show_about'] ?? true;
        $showGallery = $ts['show_gallery'] ?? true;
        $showContact = $ts['show_contact'] ?? true;
    @endphp
    <div class="tp-wrap" style="--tp-accent: {{ $accent }};">

        {{-- Hero / Banner --}}
        <div class="tp-hero" style="background: linear-gradient(160deg, #201a2e, #0d0f17);">
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

        {{-- Services --}}
        @if ($showServices && $services->count())
            <div class="tp-section">
                <div class="tp-section-title">Services</div>
                <div class="tp-svc-grid">
                    @foreach ($services as $service)
                        <div class="tp-svc-card">
                            @if ($service->photo)
                                <img class="tp-svc-photo" src="{{ Storage::url($service->photo) }}" alt="{{ $service->name }}">
                            @endif
                            <div class="tp-svc-name">{{ $service->name }}</div>
                            @if ($service->description)
                                <div class="tp-svc-desc">{{ $service->description }}</div>
                            @endif
                            @if ($service->price !== null)
                                <div class="tp-svc-price">₹{{ number_format($service->price, 2) }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Testimonials --}}
        @if ($showTestimonials && $testimonials->count())
            <div class="tp-section">
                <div class="tp-section-title">What Clients Say</div>
                <div class="tp-test-grid">
                    @foreach ($testimonials as $t)
                        <div class="tp-test-card">
                            @if ($t->rating)
                                <div class="tp-test-stars">{{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}</div>
                            @endif
                            <div class="tp-test-quote">&ldquo;{{ $t->content }}&rdquo;</div>
                            <div class="tp-test-author">
                                @if ($t->photo)
                                    <img class="tp-test-photo" src="{{ Storage::url($t->photo) }}" alt="{{ $t->author_name }}">
                                @endif
                                <div>
                                    <div class="tp-test-name">{{ $t->author_name }}</div>
                                    @if ($t->author_role)
                                        <div class="tp-test-role">{{ $t->author_role }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Blog --}}
        @if ($showBlog && $posts->count())
            <div class="tp-section">
                <div class="tp-section-title">Latest Posts</div>
                <div class="tp-blog-grid">
                    @foreach ($posts as $post)
                        <div class="tp-blog-card">
                            @if ($post->cover_photo)
                                <img class="tp-blog-photo" src="{{ Storage::url($post->cover_photo) }}" alt="{{ $post->title }}">
                            @endif
                            <div class="tp-blog-body">
                                <div class="tp-blog-title">{{ $post->title }}</div>
                                @if ($post->excerpt)
                                    <div class="tp-blog-excerpt">{{ $post->excerpt }}</div>
                                @endif
                                <div class="tp-blog-date">{{ $post->published_at?->format('M j, Y') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
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
</body>
</html>
