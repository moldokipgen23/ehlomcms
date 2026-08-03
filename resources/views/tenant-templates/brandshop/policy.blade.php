<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }} — {{ $page['title'] }}</title>
    @if (($tenant->theme_settings['favicon'] ?? null))
        <link rel="icon" href="{{ Storage::url($tenant->theme_settings['favicon']) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .policy-wrap { min-height: 100vh; background: #f7f9fd; color: #172033; }
        .policy-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 28px; border-bottom: 1px solid #e5edf7; background: #fff; }
        .policy-brand { display: flex; align-items: center; gap: 10px; color: #172033; text-decoration: none; font-weight: 900; }
        .policy-brand img { width: 36px; height: 36px; border-radius: 9px; object-fit: cover; }
        .policy-back { color: #475569; text-decoration: none; font-size: 13px; font-weight: 800; }
        .policy-main { max-width: 860px; margin: 0 auto; padding: 36px 24px 52px; }
        .policy-kicker { color: var(--tp-accent, #2563eb); font-size: 11px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; }
        .policy-title { margin-top: 8px; font-family: 'Syne', sans-serif; font-size: 34px; font-weight: 800; }
        .policy-card { margin-top: 18px; padding: 24px; border: 1px solid #dfe7f2; border-radius: 12px; background: #fff; box-shadow: 0 14px 34px rgba(15,23,42,.06); }
        .policy-content { white-space: pre-wrap; color: #334155; font-size: 14px; line-height: 1.8; }
        .policy-empty { color: #64748b; font-size: 14px; line-height: 1.7; }
        @media (max-width: 640px) {
            .policy-header { padding: 14px 16px; }
            .policy-main { padding: 28px 16px 42px; }
            .policy-title { font-size: 28px; }
            .policy-card { padding: 18px; }
        }
    </style>
</head>
<body>
@php $accent = $tenant->theme_settings['accent_color'] ?? '#2563eb'; @endphp
<div class="policy-wrap" style="--tp-accent: {{ $accent }};">
    <header class="policy-header">
        <a class="policy-brand" href="{{ route('tenant.home') }}">
            @if ($tenant->logo)
                <img src="{{ Storage::url($tenant->logo) }}" alt="{{ $tenant->name }}">
            @endif
            <span>{{ $tenant->name }}</span>
        </a>
        <a class="policy-back" href="{{ route('tenant.home') }}"><i class="ti ti-arrow-left"></i> Back to Store</a>
    </header>
    <main class="policy-main">
        <div class="policy-kicker">Store Policy</div>
        <h1 class="policy-title">{{ $page['title'] }}</h1>
        <section class="policy-card">
            @if ($content)
                <div class="policy-content">{{ $content }}</div>
            @else
                <div class="policy-empty">This policy has not been published yet. Please contact {{ $tenant->name }} for details.</div>
            @endif
        </section>
    </main>
</div>
</body>
</html>
