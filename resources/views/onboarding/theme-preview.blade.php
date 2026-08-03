@php
    $industry = $theme->industries[0] ?? 'general';
    $meta = match ($industry) {
        'shopping' => ['label' => 'Shopping / Store', 'accent' => '#2563eb', 'soft' => '#eff6ff', 'icon' => 'shopping-bag'],
        'restaurant' => ['label' => 'Restaurant', 'accent' => '#c2410c', 'soft' => '#fff7ed', 'icon' => 'tools-kitchen-2'],
        'business' => ['label' => 'Portfolio / Business', 'accent' => '#0f766e', 'soft' => '#ecfdf5', 'icon' => 'briefcase-2'],
        'school' => ['label' => 'School', 'accent' => '#7c3aed', 'soft' => '#f5f3ff', 'icon' => 'school'],
        'info' => ['label' => 'Information', 'accent' => '#0369a1', 'soft' => '#f0f9ff', 'icon' => 'file-description'],
        default => ['label' => 'Cross-business', 'accent' => '#475569', 'soft' => '#f8fafc', 'icon' => 'layout-dashboard'],
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $theme->name }} - Theme Preview</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        :root { --accent: {{ $meta['accent'] }}; --soft: {{ $meta['soft'] }}; --ink: #172033; --muted: #64748b; --line: #e2e8f0; }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); background: #f8fafc; font: 14px/1.55 Inter, system-ui, sans-serif; }
        .top { position: sticky; top: 0; z-index: 2; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:12px 20px; color:#fff; background:#111827; }
        .top strong { font-size:14px; }
        .top small { color:#cbd5e1; }
        .top a { color:#fff; text-decoration:none; border:1px solid #475569; border-radius:7px; padding:7px 11px; font-weight:700; }
        .wrap { max-width:1120px; margin:0 auto; padding:28px 20px 60px; }
        .identity { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; padding:20px; border:1px solid var(--line); border-radius:12px; background:#fff; }
        .identity-main { display:flex; gap:14px; align-items:center; }
        .identity-icon { width:52px; height:52px; border-radius:14px; display:grid; place-items:center; color:#fff; background:var(--accent); font-size:25px; }
        h1,h2,h3,p { margin-top:0; }
        h1 { margin-bottom:5px; font-size:24px; }
        h2 { font-size:22px; margin-bottom:18px; }
        .eyebrow { color:var(--accent); font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
        .muted { color:var(--muted); }
        .preview { margin-top:18px; overflow:hidden; border:1px solid var(--line); border-radius:14px; background:#fff; box-shadow:0 14px 32px rgba(15,23,42,.08); }
        .site-nav { display:flex; align-items:center; justify-content:space-between; gap:18px; padding:17px 22px; border-bottom:1px solid var(--line); }
        .site-brand { font-size:18px; font-weight:800; }
        .site-links { display:flex; gap:17px; color:var(--muted); font-size:12px; font-weight:700; }
        .site-hero { padding:58px 34px; background:var(--soft); }
        .site-hero h2 { max-width:680px; margin:8px 0 10px; font-size:clamp(28px,4vw,48px); line-height:1.06; }
        .site-hero p { max-width:620px; color:var(--muted); font-size:16px; }
        .button { display:inline-flex; padding:10px 14px; border-radius:7px; color:#fff; background:var(--accent); text-decoration:none; font-weight:800; }
        .section { padding:30px 34px; }
        .grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
        .card { padding:16px; border:1px solid var(--line); border-radius:9px; background:#fff; }
        .card-media { display:grid; place-items:center; height:120px; margin:-16px -16px 14px; color:var(--accent); background:var(--soft); font-size:34px; }
        .card h3 { margin-bottom:5px; font-size:15px; }
        .card p { margin-bottom:0; color:var(--muted); font-size:12px; }
        .menu-item { display:flex; justify-content:space-between; gap:15px; padding:14px 0; border-bottom:1px solid var(--line); }
        .price { color:var(--accent); font-weight:800; white-space:nowrap; }
        .footer { padding:22px 34px; color:#cbd5e1; background:#111827; }
        @media (max-width:700px) { .identity { flex-direction:column; } .site-links { display:none; } .grid { grid-template-columns:1fr; } .site-hero,.section { padding:26px 20px; } }
    </style>
</head>
<body>
    <div class="top">
        <div><strong>{{ $theme->name }}</strong> <small> / {{ $meta['label'] }} theme preview</small></div>
        <a href="{{ route('themes.index') }}">Back to themes</a>
    </div>
    <main class="wrap">
        <section class="identity">
            <div class="identity-main">
                <div class="identity-icon"><i class="ti ti-{{ $meta['icon'] }}"></i></div>
                <div><div class="eyebrow">{{ $meta['label'] }}</div><h1>{{ $theme->name }}</h1><div class="muted">{{ $theme->description ?: 'Reusable storefront or website theme.' }}</div></div>
            </div>
            <div class="muted" style="text-align:right;font-size:12px;">Key: {{ $theme->key }}<br>Template: {{ $theme->base_template ?: 'Custom HTML' }}</div>
        </section>

        @if ($theme->custom_html)
            <section class="preview" style="margin-top:18px;">
                {!! $theme->custom_html !!}
            </section>
        @else
            <section class="preview">
                <div class="site-nav"><div class="site-brand">{{ $demoTenant->name }}</div><div class="site-links"><span>Home</span><span>About</span><span>{{ $industry === 'shopping' ? 'Catalog' : ($industry === 'restaurant' ? 'Menu' : 'Contact') }}</span><span>Contact</span></div><i class="ti ti-menu-2"></i></div>
                @switch($industry)
                    @case('shopping')
                        <div class="site-hero"><div class="eyebrow">New collection</div><h2>Products presented with a storefront built to sell.</h2><p>Catalog, product details, collections, cart, checkout and customer-friendly shopping flow.</p><a class="button" href="#preview-catalog">Browse products</a></div>
                        <div class="section" id="preview-catalog"><h2>Featured products</h2><div class="grid">@foreach(['Signature shirt','Everyday tote','Modern accessories'] as $item)<article class="card"><div class="card-media"><i class="ti ti-shirt"></i></div><h3>{{ $item }}</h3><p>Demo product presentation with clear details and pricing.</p><div class="price" style="margin-top:12px;">₹1,299</div></article>@endforeach</div></div>
                        @break
                    @case('restaurant')
                        <div class="site-hero"><div class="eyebrow">Freshly prepared</div><h2>A welcoming menu experience for your guests.</h2><p>Menu categories, dish presentation, ordering actions, reservations and contact details in one clear site.</p><a class="button" href="#preview-menu">View menu</a></div>
                        <div class="section" id="preview-menu"><h2>Today's menu</h2>@foreach(['Seasonal tasting plate','Charred garden vegetables','House dessert'] as $item)<div class="menu-item"><div><strong>{{ $item }}</strong><div class="muted">A short description for the dish and its ingredients.</div></div><div class="price">₹499</div></div>@endforeach</div>
                        @break
                    @case('business')
                        <div class="site-hero"><div class="eyebrow">Selected work and services</div><h2>A credible professional website for a growing business.</h2><p>Services, case studies, team, testimonials, insights, enquiries and custom pages are arranged for easy browsing.</p><a class="button" href="#preview-work">Explore work</a></div>
                        <div class="section" id="preview-work"><h2>What we do</h2><div class="grid">@foreach(['Strategy and direction','Brand and digital','Projects with results'] as $item)<article class="card"><div class="card-media"><i class="ti ti-briefcase"></i></div><h3>{{ $item }}</h3><p>Demo service or project content managed from the client dashboard.</p></article>@endforeach</div></div>
                        @break
                    @case('school')
                        <div class="site-hero"><div class="eyebrow">Learning with purpose</div><h2>A clear, welcoming school website for families.</h2><p>Academics, admissions, faculty, facilities, news, gallery, downloads, policies and enquiry forms.</p><a class="button" href="#preview-school">Explore the school</a></div>
                        <div class="section" id="preview-school"><h2>School information</h2><div class="grid">@foreach(['Academics','Admissions','Faculty and staff'] as $item)<article class="card"><div class="card-media"><i class="ti ti-school"></i></div><h3>{{ $item }}</h3><p>Demo public information section that the school team can maintain.</p></article>@endforeach</div></div>
                        @break
                    @case('info')
                        <div class="site-hero"><div class="eyebrow">Welcome</div><h2>A simple information site that keeps important details easy to find.</h2><p>About content, gallery, contact information, policies and custom pages in a focused layout.</p><a class="button" href="#preview-info">Learn more</a></div>
                        <div class="section" id="preview-info"><h2>About the organisation</h2><p class="muted" style="max-width:720px;">This is an information-first theme, not a shopping storefront. Replace this demo copy with the approved client content.</p></div>
                        @break
                    @default
                        <div class="site-hero"><div class="eyebrow">{{ $meta['label'] }}</div><h2>A reusable website theme for your next client.</h2><p>This preview follows the theme's assigned business type and template.</p></div>
                @endswitch
                <div class="footer">{{ $demoTenant->name }} · Preview content only</div>
            </section>
        @endif
    </main>
</body>
</html>
