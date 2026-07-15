<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }}</title>
    <meta name="description" content="{{ $tenant->about_text ?? $tenant->name . ' - Official Website' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --sc-accent: {{ $accent }}; --sc-accent-light: {{ $accent }}15; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: #1e293b; background: #f8fafc; }
        .sc-section { padding: 64px 32px; }
        .sc-section-alt { background: white; }
        .sc-container { max-width: 1200px; margin: 0 auto; }
        .sc-title { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 700; text-align: center; margin-bottom: 8px; }
        .sc-subtitle { text-align: center; color: #64748b; font-size: 14px; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto; }
        .sc-badge { display: inline-block; background: var(--sc-accent-light); color: var(--sc-accent); font-size: 11px; font-weight: 600; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
        .sc-grid { display: grid; gap: 20px; }
        .sc-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .sc-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .sc-grid-4 { grid-template-columns: repeat(4, 1fr); }
        .sc-card { background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; }
        .sc-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; text-decoration: none; transition: all 0.2s; }
        .sc-btn-primary { background: var(--sc-accent); color: white; }
        .sc-btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .sc-btn-outline { border: 2px solid var(--sc-accent); color: var(--sc-accent); background: none; }
        .sc-btn-outline:hover { background: var(--sc-accent); color: white; }
        .sc-footer { background: #0f172a; color: #94a3b8; padding: 48px 32px 24px; }
        .sc-footer a { color: #cbd5e1; text-decoration: none; }
        .sc-footer a:hover { color: white; }
        @media (max-width: 768px) {
            .sc-grid-2, .sc-grid-3, .sc-grid-4 { grid-template-columns: 1fr; }
            .sc-section { padding: 40px 16px; }
        }
    </style>
    @yield('school-styles')
</head>
<body>
@php
    $s = $tenant->theme_settings ?? [];
    $accent = $s['accent_color'] ?? '#1e40af';
@endphp

{{-- ═══════════════ NAVIGATION ═══════════════ --}}
@if ($s['show_nav'] ?? true)
<nav style="background:white;border-bottom:1px solid #e2e8f0;padding:12px 32px;position:sticky;top:0;z-index:100;">
    <div class="sc-container" style="display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            @if ($tenant->logo)
                <img src="{{ Storage::url($tenant->logo) }}" alt="Logo" style="height:40px;">
            @endif
            <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:18px;color:#0f172a;">{{ $tenant->name }}</span>
        </div>
        <div style="display:flex;gap:24px;font-size:13px;font-weight:500;">
            <a href="#home" style="color:#475569;text-decoration:none;">Home</a>
            <a href="#about" style="color:#475569;text-decoration:none;">About</a>
            <a href="#academics" style="color:#475569;text-decoration:none;">Academics</a>
            <a href="#admissions" style="color:#475569;text-decoration:none;">Admissions</a>
            <a href="#faculty" style="color:#475569;text-decoration:none;">Faculty</a>
            <a href="#student-life" style="color:#475569;text-decoration:none;">Student Life</a>
            <a href="#gallery" style="color:#475569;text-decoration:none;">Gallery</a>
            <a href="#news" style="color:#475569;text-decoration:none;">News</a>
            <a href="#contact" style="color:#475569;text-decoration:none;">Contact</a>
        </div>
    </div>
</nav>
@endif

{{-- ═══════════════ HERO BANNER ═══════════════ --}}
@if ($s['show_hero'] ?? true)
<section id="home" style="position:relative;min-height:520px;display:flex;align-items:center;overflow:hidden;background:linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);">
    @if ($tenant->banner_image)
        <img src="{{ Storage::url($tenant->banner_image) }}" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
        <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(15,23,42,0.85),rgba(30,58,95,0.75));"></div>
    @endif
    <div class="sc-container" style="position:relative;z-index:1;text-align:center;color:white;padding:60px 0;">
        @if ($s['school_motto'] ?? null)
            <div style="font-size:13px;letter-spacing:3px;text-transform:uppercase;opacity:0.8;margin-bottom:16px;">{{ $s['school_motto'] }}</div>
        @endif
        <h1 style="font-family:'Syne',sans-serif;font-size:48px;font-weight:800;margin-bottom:16px;line-height:1.1;">{{ $tenant->name }}</h1>
        @if ($s['hero_tagline'] ?? null)
            <p style="font-size:18px;opacity:0.85;max-width:600px;margin:0 auto 32px;">{{ $s['hero_tagline'] }}</p>
        @endif
        @if ($s['show_admission_banner'] ?? true)
            <div style="display:inline-block;background:var(--sc-accent);padding:12px 32px;border-radius:8px;font-weight:700;font-size:15px;margin-bottom:24px;">
                <i class="ti ti-school"></i> Admissions Open {{ $s['admission_year'] ?? '2026-27' }}
            </div>
            <br>
        @endif
        @if ($s['admission_cta_url'] ?? null)
            <a href="{{ $s['admission_cta_url'] }}" class="sc-btn sc-btn-primary" style="font-size:16px;padding:14px 36px;">Apply Now <i class="ti ti-arrow-right"></i></a>
        @endif
    </div>
</section>
@endif

{{-- ═══════════════ SCHOOL HIGHLIGHTS ═══════════════ --}}
@if ($s['show_highlights'] ?? true)
<section class="sc-section sc-section-alt">
    <div class="sc-container">
        <div class="sc-grid sc-grid-4" style="text-align:center;">
            @foreach (['highlights_1', 'highlights_2', 'highlights_3', 'highlights_4'] as $key => $hKey)
                @if ($s[$hKey . '_title'] ?? null)
                    <div>
                        <i class="ti {{ $s[$hKey . '_icon'] ?? 'ti-star' }}" style="font-size:32px;color:var(--sc-accent);margin-bottom:12px;display:block;"></i>
                        <div style="font-weight:700;font-size:15px;margin-bottom:4px;">{{ $s[$hKey . '_title'] }}</div>
                        <div style="font-size:13px;color:#64748b;">{{ $s[$hKey . '_desc'] ?? '' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ PRINCIPAL'S WELCOME ═══════════════ --}}
@if ($s['show_principal'] ?? true)
@if ($s['principal_name'] ?? null)
<section class="sc-section">
    <div class="sc-container" style="display:flex;align-items:center;gap:40px;max-width:900px;">
        @if ($s['principal_photo'] ?? null)
            <div style="flex:0 0 160px;">
                <img src="{{ Storage::url($s['principal_photo']) }}" alt="{{ $s['principal_name'] }}" style="width:160px;height:160px;border-radius:50%;object-fit:cover;border:4px solid var(--sc-accent-light);">
            </div>
        @endif
        <div>
            <div class="sc-badge">Principal's Message</div>
            <h2 style="font-family:'Syne',sans-serif;font-size:22px;font-weight:700;margin-bottom:8px;">{{ $s['principal_name'] }}</h2>
            @if ($s['principal_title'] ?? null)
                <div style="font-size:13px;color:#64748b;margin-bottom:12px;">{{ $s['principal_title'] }}</div>
            @endif
            <p style="font-size:14px;color:#475569;line-height:1.8;white-space:pre-wrap;">{{ $s['principal_message'] ?? '' }}</p>
        </div>
    </div>
</section>
@endif
@endif

{{-- ═══════════════ WHY CHOOSE US ═══════════════ --}}
@if ($s['show_why_choose'] ?? true)
<section class="sc-section sc-section-alt">
    <div class="sc-container">
        <div class="sc-badge" style="text-align:center;display:block;">Why Choose Us</div>
        <h2 class="sc-title">{{ $s['why_choose_title'] ?? 'Why Choose ' . $tenant->name }}</h2>
        <div class="sc-grid sc-grid-3" style="margin-top:32px;">
            @foreach (['reason_1', 'reason_2', 'reason_3', 'reason_4', 'reason_5', 'reason_6'] as $rKey)
                @if ($s[$rKey . '_title'] ?? null)
                    <div class="sc-card" style="text-align:center;">
                        <i class="ti {{ $s[$rKey . '_icon'] ?? 'ti-check' }}" style="font-size:28px;color:var(--sc-accent);margin-bottom:12px;display:block;"></i>
                        <div style="font-weight:700;font-size:15px;margin-bottom:6px;">{{ $s[$rKey . '_title'] }}</div>
                        <div style="font-size:13px;color:#64748b;line-height:1.6;">{{ $s[$rKey . '_desc'] ?? '' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ STATISTICS ═══════════════ --}}
@if ($s['show_stats'] ?? true)
<section class="sc-section" style="background:linear-gradient(135deg,#1e3a5f,#0f172a);color:white;">
    <div class="sc-container">
        <div class="sc-grid sc-grid-5" style="text-align:center;grid-template-columns:repeat(5,1fr);">
            @foreach (['stat_1', 'stat_2', 'stat_3', 'stat_4', 'stat_5'] as $stKey)
                @if ($s[$stKey . '_value'] ?? null)
                    <div>
                        <div style="font-family:'Syne',sans-serif;font-size:36px;font-weight:800;color:var(--sc-accent);">{{ $s[$stKey . '_value'] }}</div>
                        <div style="font-size:13px;opacity:0.8;margin-top:4px;">{{ $s[$stKey . '_label'] ?? '' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ ABOUT ═══════════════ --}}
@if ($s['show_about'] ?? true)
<section id="about" class="sc-section sc-section-alt">
    <div class="sc-container" style="max-width:900px;text-align:center;">
        <div class="sc-badge">About Us</div>
        <h2 class="sc-title">{{ $s['about_title'] ?? 'About ' . $tenant->name }}</h2>
        @if ($tenant->about_text)
            <p style="font-size:15px;color:#475569;line-height:1.8;margin-top:20px;white-space:pre-wrap;">{{ $tenant->about_text }}</p>
        @endif
        <div class="sc-grid sc-grid-3" style="margin-top:32px;text-align:left;">
            @if ($s['vision'] ?? null)
                <div class="sc-card">
                    <i class="ti ti-eye" style="font-size:24px;color:var(--sc-accent);margin-bottom:12px;display:block;"></i>
                    <div style="font-weight:700;font-size:15px;margin-bottom:6px;">Our Vision</div>
                    <div style="font-size:13px;color:#64748b;line-height:1.6;">{{ $s['vision'] }}</div>
                </div>
            @endif
            @if ($s['mission'] ?? null)
                <div class="sc-card">
                    <i class="ti ti-target-arrow" style="font-size:24px;color:var(--sc-accent);margin-bottom:12px;display:block;"></i>
                    <div style="font-weight:700;font-size:15px;margin-bottom:6px;">Our Mission</div>
                    <div style="font-size:13px;color:#64748b;line-height:1.6;">{{ $s['mission'] }}</div>
                </div>
            @endif
            @if ($s['core_values'] ?? null)
                <div class="sc-card">
                    <i class="ti ti-heart" style="font-size:24px;color:var(--sc-accent);margin-bottom:12px;display:block;"></i>
                    <div style="font-weight:700;font-size:15px;margin-bottom:6px;">Core Values</div>
                    <div style="font-size:13px;color:#64748b;line-height:1.6;">{{ $s['core_values'] }}</div>
                </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ ACADEMICS ═══════════════ --}}
@if ($s['show_academics'] ?? true)
<section id="academics" class="sc-section">
    <div class="sc-container">
        <div class="sc-badge" style="text-align:center;display:block;">Academics</div>
        <h2 class="sc-title">{{ $s['academics_title'] ?? 'Academic Excellence' }}</h2>
        <div class="sc-grid sc-grid-2" style="margin-top:32px;max-width:900px;margin-left:auto;margin-right:auto;">
            @foreach (['academics_1', 'academics_2', 'academics_3', 'academics_4'] as $aKey)
                @if ($s[$aKey . '_title'] ?? null)
                    <div class="sc-card" style="display:flex;gap:16px;align-items:flex-start;">
                        <i class="ti {{ $s[$aKey . '_icon'] ?? 'ti-book' }}" style="font-size:24px;color:var(--sc-accent);flex-shrink:0;margin-top:2px;"></i>
                        <div>
                            <div style="font-weight:700;font-size:15px;margin-bottom:4px;">{{ $s[$aKey . '_title'] }}</div>
                            <div style="font-size:13px;color:#64748b;line-height:1.6;">{{ $s[$aKey . '_desc'] ?? '' }}</div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        @if ($s['school_timings'] ?? null)
            <div style="margin-top:24px;text-align:center;font-size:14px;color:#475569;">
                <i class="ti ti-clock"></i> <strong>School Timings:</strong> {{ $s['school_timings'] }}
            </div>
        @endif
    </div>
</section>
@endif

{{-- ═══════════════ ADMISSIONS ═══════════════ --}}
@if ($s['show_admissions'] ?? true)
<section id="admissions" class="sc-section sc-section-alt">
    <div class="sc-container" style="max-width:900px;">
        <div class="sc-badge" style="text-align:center;display:block;">Admissions</div>
        <h2 class="sc-title">{{ $s['admissions_title'] ?? 'Admissions Open ' . ($s['admission_year'] ?? '2026-27') }}</h2>
        @if ($s['admission_process'] ?? null)
            <div style="margin-top:24px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:12px;">Admission Process</h3>
                <p style="font-size:14px;color:#475569;line-height:1.8;white-space:pre-wrap;">{{ $s['admission_process'] }}</p>
            </div>
        @endif
        @if ($s['fee_structure'] ?? null)
            <div style="margin-top:24px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:12px;">Fee Structure</h3>
                <p style="font-size:14px;color:#475569;line-height:1.8;white-space:pre-wrap;">{{ $s['fee_structure'] }}</p>
            </div>
        @endif
        @if ($s['required_documents'] ?? null)
            <div style="margin-top:24px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:12px;">Required Documents</h3>
                <p style="font-size:14px;color:#475569;line-height:1.8;white-space:pre-wrap;">{{ $s['required_documents'] }}</p>
            </div>
        @endif
        @if ($s['admission_faq'] ?? null)
            <div style="margin-top:24px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:12px;">FAQs</h3>
                <p style="font-size:14px;color:#475569;line-height:1.8;white-space:pre-wrap;">{{ $s['admission_faq'] }}</p>
            </div>
        @endif
        @if ($s['admission_enquiry_url'] ?? null)
            <div style="margin-top:24px;text-align:center;">
                <a href="{{ $s['admission_enquiry_url'] }}" class="sc-btn sc-btn-primary">Enquire Now <i class="ti ti-arrow-right"></i></a>
            </div>
        @endif
    </div>
</section>
@endif

{{-- ═══════════════ FACULTY ═══════════════ --}}
@if ($s['show_faculty'] ?? true)
<section id="faculty" class="sc-section">
    <div class="sc-container">
        <div class="sc-badge" style="text-align:center;display:block;">Our Team</div>
        <h2 class="sc-title">{{ $s['faculty_title'] ?? 'Faculty & Staff' }}</h2>
        <div class="sc-grid sc-grid-4" style="margin-top:32px;">
            @foreach (['faculty_1', 'faculty_2', 'faculty_3', 'faculty_4', 'faculty_5', 'faculty_6', 'faculty_7', 'faculty_8'] as $fKey)
                @if ($s[$fKey . '_name'] ?? null)
                    <div style="text-align:center;">
                        @if ($s[$fKey . '_photo'] ?? null)
                            <img src="{{ Storage::url($s[$fKey . '_photo']) }}" alt="{{ $s[$fKey . '_name'] }}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:12px;border:3px solid var(--sc-accent-light);">
                        @else
                            <div style="width:120px;height:120px;border-radius:50%;background:var(--sc-accent-light);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                                <span style="font-size:32px;font-weight:700;color:var(--sc-accent);">{{ strtoupper(substr($s[$fKey . '_name'], 0, 1)) }}</span>
                            </div>
                        @endif
                        <div style="font-weight:700;font-size:14px;">{{ $s[$fKey . '_name'] }}</div>
                        <div style="font-size:12px;color:var(--sc-accent);font-weight:500;">{{ $s[$fKey . '_role'] ?? '' }}</div>
                        @if ($s[$fKey . '_qualification'] ?? null)
                            <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ $s[$fKey . '_qualification'] }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ STUDENT LIFE ═══════════════ --}}
@if ($s['show_student_life'] ?? true)
<section id="student-life" class="sc-section sc-section-alt">
    <div class="sc-container">
        <div class="sc-badge" style="text-align:center;display:block;">Student Life</div>
        <h2 class="sc-title">{{ $s['student_life_title'] ?? 'Life Beyond Classrooms' }}</h2>
        <div class="sc-grid sc-grid-3" style="margin-top:32px;">
            @foreach (['activity_1', 'activity_2', 'activity_3', 'activity_4', 'activity_5', 'activity_6'] as $actKey)
                @if ($s[$actKey . '_title'] ?? null)
                    <div class="sc-card" style="text-align:center;">
                        <i class="ti {{ $s[$actKey . '_icon'] ?? 'ti-star' }}" style="font-size:28px;color:var(--sc-accent);margin-bottom:12px;display:block;"></i>
                        <div style="font-weight:700;font-size:15px;margin-bottom:4px;">{{ $s[$actKey . '_title'] }}</div>
                        <div style="font-size:13px;color:#64748b;line-height:1.6;">{{ $s[$actKey . '_desc'] ?? '' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ FACILITIES ═══════════════ --}}
@if ($s['show_facilities'] ?? true)
<section class="sc-section">
    <div class="sc-container">
        <div class="sc-badge" style="text-align:center;display:block;">Infrastructure</div>
        <h2 class="sc-title">{{ $s['facilities_title'] ?? 'Our Facilities' }}</h2>
        <div class="sc-grid sc-grid-4" style="margin-top:32px;">
            @foreach (['facility_1', 'facility_2', 'facility_3', 'facility_4', 'facility_5', 'facility_6', 'facility_7', 'facility_8'] as $facKey)
                @if ($s[$facKey . '_name'] ?? null)
                    <div class="sc-card" style="text-align:center;">
                        <i class="ti {{ $s[$facKey . '_icon'] ?? 'ti-building' }}" style="font-size:28px;color:var(--sc-accent);margin-bottom:10px;display:block;"></i>
                        <div style="font-weight:700;font-size:14px;">{{ $s[$facKey . '_name'] }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ GALLERY ═══════════════ --}}
@if ($s['show_gallery'] ?? true)
@if ($tenant->galleryImages->count())
<section id="gallery" class="sc-section sc-section-alt">
    <div class="sc-container">
        <div class="sc-badge" style="text-align:center;display:block;">Gallery</div>
        <h2 class="sc-title">{{ $s['gallery_title'] ?? 'Campus Gallery' }}</h2>
        <div class="sc-grid sc-grid-4" style="margin-top:32px;">
            @foreach ($tenant->galleryImages->take(8) as $image)
                <div style="border-radius:12px;overflow:hidden;aspect-ratio:4/3;">
                    <img src="{{ Storage::url($image->image_path) }}" alt="{{ $image->caption ?? '' }}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endif

{{-- ═══════════════ NEWS & EVENTS ═══════════════ --}}
@if ($s['show_news'] ?? true)
<section id="news" class="sc-section">
    <div class="sc-container">
        <div class="sc-grid sc-grid-2" style="max-width:900px;margin:0 auto;">
            {{-- Latest News --}}
            <div>
                <div class="sc-badge">Latest News</div>
                <h3 style="font-family:'Syne',sans-serif;font-size:20px;font-weight:700;margin-bottom:16px;">{{ $s['news_title'] ?? 'News & Notices' }}</h3>
                @foreach (['news_1', 'news_2', 'news_3'] as $nKey)
                    @if ($s[$nKey . '_title'] ?? null)
                        <div style="padding:12px 0;border-bottom:1px solid #e2e8f0;">
                            <div style="font-size:11px;color:#94a3b8;">{{ $s[$nKey . '_date'] ?? '' }}</div>
                            <div style="font-weight:600;font-size:14px;margin-top:2px;">{{ $s[$nKey . '_title'] }}</div>
                            <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $s[$nKey . '_excerpt'] ?? '' }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
            {{-- Upcoming Events --}}
            <div>
                <div class="sc-badge">Events</div>
                <h3 style="font-family:'Syne',sans-serif;font-size:20px;font-weight:700;margin-bottom:16px;">{{ $s['events_title'] ?? 'Upcoming Events' }}</h3>
                @foreach (['event_1', 'event_2', 'event_3'] as $eKey)
                    @if ($s[$eKey . '_title'] ?? null)
                        <div style="padding:12px 0;border-bottom:1px solid #e2e8f0;display:flex;gap:12px;">
                            <div style="flex:0 0 48px;text-align:center;background:var(--sc-accent-light);border-radius:8px;padding:8px;">
                                <div style="font-size:18px;font-weight:800;color:var(--sc-accent);">{{ $s[$eKey . '_day'] ?? '' }}</div>
                                <div style="font-size:10px;color:var(--sc-accent);text-transform:uppercase;">{{ $s[$eKey . '_month'] ?? '' }}</div>
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:14px;">{{ $s[$eKey . '_title'] }}</div>
                                <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $s[$eKey . '_desc'] ?? '' }}</div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ ACHIEVEMENTS ═══════════════ --}}
@if ($s['show_achievements'] ?? true)
<section class="sc-section sc-section-alt">
    <div class="sc-container">
        <div class="sc-badge" style="text-align:center;display:block;">Achievements</div>
        <h2 class="sc-title">{{ $s['achievements_title'] ?? 'Our Achievements' }}</h2>
        <div class="sc-grid sc-grid-3" style="margin-top:32px;">
            @foreach (['achievement_1', 'achievement_2', 'achievement_3', 'achievement_4', 'achievement_5', 'achievement_6'] as $achKey)
                @if ($s[$achKey . '_title'] ?? null)
                    <div class="sc-card" style="text-align:center;">
                        <i class="ti ti-trophy" style="font-size:28px;color:var(--sc-accent);margin-bottom:10px;display:block;"></i>
                        <div style="font-weight:700;font-size:14px;margin-bottom:4px;">{{ $s[$achKey . '_title'] }}</div>
                        <div style="font-size:12px;color:#64748b;">{{ $s[$achKey . '_desc'] ?? '' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ TESTIMONIALS ═══════════════ --}}
@if ($s['show_testimonials'] ?? true)
<section class="sc-section">
    <div class="sc-container">
        <div class="sc-badge" style="text-align:center;display:block;">Testimonials</div>
        <h2 class="sc-title">{{ $s['testimonials_title'] ?? 'What Parents Say' }}</h2>
        <div class="sc-grid sc-grid-3" style="margin-top:32px;">
            @foreach (['testimonial_1', 'testimonial_2', 'testimonial_3'] as $tKey)
                @if ($s[$tKey . '_name'] ?? null)
                    <div class="sc-card" style="text-align:center;">
                        <div style="color:#f59e0b;font-size:14px;margin-bottom:8px;">★★★★★</div>
                        <p style="font-size:13px;color:#475569;line-height:1.7;font-style:italic;margin-bottom:12px;">"{{ $s[$tKey . '_quote'] ?? '' }}"</p>
                        <div style="font-weight:700;font-size:13px;">{{ $s[$tKey . '_name'] }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $s[$tKey . '_role'] ?? 'Parent' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ DOWNLOADS ═══════════════ --}}
@if ($s['show_downloads'] ?? true)
<section class="sc-section sc-section-alt">
    <div class="sc-container" style="max-width:800px;">
        <div class="sc-badge" style="text-align:center;display:block;">Downloads</div>
        <h2 class="sc-title">{{ $s['downloads_title'] ?? 'Important Downloads' }}</h2>
        <div style="margin-top:24px;">
            @foreach (['download_1', 'download_2', 'download_3', 'download_4', 'download_5'] as $dKey)
                @if ($s[$dKey . '_name'] ?? null)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:8px;background:white;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <i class="ti ti-file-download" style="font-size:20px;color:var(--sc-accent);"></i>
                            <span style="font-weight:600;font-size:14px;">{{ $s[$dKey . '_name'] }}</span>
                        </div>
                        @if ($s[$dKey . '_url'] ?? null)
                            <a href="{{ $s[$dKey . '_url'] }}" target="_blank" class="sc-btn sc-btn-outline" style="padding:6px 16px;font-size:12px;">Download</a>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ CERTIFICATES ═══════════════ --}}
@if ($s['show_certificates'] ?? true)
<section class="sc-section">
    <div class="sc-container" style="max-width:800px;text-align:center;">
        <div class="sc-badge" style="display:block;">Recognition</div>
        <h2 class="sc-title">{{ $s['certificates_title'] ?? 'Certificates & Recognition' }}</h2>
        <div style="margin-top:24px;display:flex;flex-wrap:wrap;justify-content:center;gap:12px;">
            @foreach (['cert_1', 'cert_2', 'cert_3', 'cert_4', 'cert_5'] as $cKey)
                @if ($s[$cKey . '_name'] ?? null)
                    <div style="display:flex;align-items:center;gap:8px;background:white;border:1px solid #e2e8f0;border-radius:8px;padding:10px 16px;">
                        <i class="ti ti-certificate" style="font-size:18px;color:var(--sc-accent);"></i>
                        <span style="font-size:13px;font-weight:600;">{{ $s[$cKey . '_name'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ CONTACT ═══════════════ --}}
@if ($s['show_contact'] ?? true)
<section id="contact" class="sc-section sc-section-alt">
    <div class="sc-container" style="max-width:900px;">
        <div class="sc-badge" style="text-align:center;display:block;">Contact Us</div>
        <h2 class="sc-title">{{ $s['contact_title'] ?? 'Get in Touch' }}</h2>
        <div class="sc-grid sc-grid-2" style="margin-top:32px;">
            <div>
                <div style="font-size:14px;color:#475569;line-height:2;">
                    @if ($tenant->contact_address)
                        <div><i class="ti ti-map-pin" style="width:20px;color:var(--sc-accent);"></i> {{ $tenant->contact_address }}</div>
                    @endif
                    @if ($tenant->contact_phone)
                        <div><i class="ti ti-phone" style="width:20px;color:var(--sc-accent);"></i> {{ $tenant->contact_phone }}</div>
                    @endif
                    @if ($tenant->contact_email)
                        <div><i class="ti ti-mail" style="width:20px;color:var(--sc-accent);"></i> {{ $tenant->contact_email }}</div>
                    @endif
                    @if ($s['office_hours'] ?? null)
                        <div><i class="ti ti-clock" style="width:20px;color:var(--sc-accent);"></i> {{ $s['office_hours'] }}</div>
                    @endif
                    @if ($s['school_code'] ?? null)
                        <div><i class="ti ti-hash" style="width:20px;color:var(--sc-accent);"></i> School Code: {{ $s['school_code'] }}</div>
                    @endif
                    @if ($s['affiliation_number'] ?? null)
                        <div><i class="ti ti-id" style="width:20px;color:var(--sc-accent);"></i> Affiliation: {{ $s['affiliation_number'] }}</div>
                    @endif
                </div>
                @if ($s['whatsapp_number'] ?? null)
                    <a href="https://wa.me/{{ $s['whatsapp_number'] }}" target="_blank" class="sc-btn sc-btn-primary" style="margin-top:20px;background:#25d366;">
                        <i class="ti ti-brand-whatsapp"></i> WhatsApp Us
                    </a>
                @endif
            </div>
            @if ($s['google_map_embed'] ?? null)
                <div style="border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
                    {!! $s['google_map_embed'] !!}
                </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ FOOTER ═══════════════ --}}
<footer class="sc-footer">
    <div class="sc-container">
        <div class="sc-grid sc-grid-4" style="margin-bottom:32px;">
            <div>
                <div style="font-weight:700;color:white;font-size:16px;margin-bottom:12px;">{{ $tenant->name }}</div>
                <div style="font-size:13px;line-height:1.7;">{{ $s['footer_about'] ?? $tenant->about_text ?? '' }}</div>
            </div>
            <div>
                <div style="font-weight:700;color:white;font-size:14px;margin-bottom:12px;">Quick Links</div>
                <div style="font-size:13px;line-height:2;">
                    <div><a href="#about">About Us</a></div>
                    <div><a href="#academics">Academics</a></div>
                    <div><a href="#admissions">Admissions</a></div>
                    <div><a href="#faculty">Faculty</a></div>
                    <div><a href="#contact">Contact</a></div>
                </div>
            </div>
            <div>
                <div style="font-weight:700;color:white;font-size:14px;margin-bottom:12px;">Important Links</div>
                <div style="font-size:13px;line-height:2;">
                    @if ($s['important_link_1_url'] ?? null)
                        <div><a href="{{ $s['important_link_1_url'] }}" target="_blank">{{ $s['important_link_1_name'] ?? 'Link 1' }}</a></div>
                    @endif
                    @if ($s['important_link_2_url'] ?? null)
                        <div><a href="{{ $s['important_link_2_url'] }}" target="_blank">{{ $s['important_link_2_name'] ?? 'Link 2' }}</a></div>
                    @endif
                    @if ($s['important_link_3_url'] ?? null)
                        <div><a href="{{ $s['important_link_3_url'] }}" target="_blank">{{ $s['important_link_3_name'] ?? 'Link 3' }}</a></div>
                    @endif
                </div>
            </div>
            <div>
                <div style="font-weight:700;color:white;font-size:14px;margin-bottom:12px;">Connect</div>
                <div style="font-size:13px;line-height:2;">
                    @if ($tenant->contact_phone)
                        <div><i class="ti ti-phone"></i> {{ $tenant->contact_phone }}</div>
                    @endif
                    @if ($tenant->contact_email)
                        <div><i class="ti ti-mail"></i> {{ $tenant->contact_email }}</div>
                    @endif
                </div>
                @if ($s['facebook_url'] ?? null || $s['instagram_url'] ?? null || $s['youtube_url'] ?? null)
                    <div style="display:flex;gap:12px;margin-top:12px;">
                        @if ($s['facebook_url'] ?? null)
                            <a href="{{ $s['facebook_url'] }}" target="_blank" style="width:36px;height:36px;border-radius:50%;background:#1e293b;display:flex;align-items:center;justify-content:center;color:#94a3b8;"><i class="ti ti-brand-facebook"></i></a>
                        @endif
                        @if ($s['instagram_url'] ?? null)
                            <a href="{{ $s['instagram_url'] }}" target="_blank" style="width:36px;height:36px;border-radius:50%;background:#1e293b;display:flex;align-items:center;justify-content:center;color:#94a3b8;"><i class="ti ti-brand-instagram"></i></a>
                        @endif
                        @if ($s['youtube_url'] ?? null)
                            <a href="{{ $s['youtube_url'] }}" target="_blank" style="width:36px;height:36px;border-radius:50%;background:#1e293b;display:flex;align-items:center;justify-content:center;color:#94a3b8;"><i class="ti ti-brand-youtube"></i></a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div style="border-top:1px solid #1e293b;padding-top:20px;text-align:center;font-size:12px;">
            &copy; {{ date('Y') }} {{ $tenant->name }}. All rights reserved.
            @if ($s['privacy_policy_url'] ?? null)
                <a href="{{ $s['privacy_policy_url'] }}" style="margin-left:12px;">Privacy Policy</a>
            @endif
            @if ($s['terms_url'] ?? null)
                <a href="{{ $s['terms_url'] }}" style="margin-left:12px;">Terms & Conditions</a>
            @endif
        </div>
    </div>
</footer>

@include('tenant-templates.partials.ai-assistant')
</body>
</html>
