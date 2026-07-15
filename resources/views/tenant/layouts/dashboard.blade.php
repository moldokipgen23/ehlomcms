<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Ehlom OS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="eos-wrap" x-data="{ open: false }">

    <aside class="eos-sidebar" :class="{ 'open': open }">
        <div class="eos-logo">
            <div class="eos-logo-icon">E</div>
            <div>
                <div class="eos-logo-text">Ehlom OS</div>
                <div class="eos-logo-sub">Client Dashboard</div>
            </div>
        </div>

        <nav class="eos-nav">
            @php
                $t = app(\App\Services\TenantContext::class)->get();
                $modules = config('modules');
                $links = [
                    'Main' => [
                        ['tenant.dashboard', 'Dashboard', 'ti-layout-dashboard'],
                    ],
                ];
                foreach ($modules as $key => $m) {
                    if (!is_array($m) || !isset($m['nav_section'])) continue;
                    if ($t && $t->hasModule($key)) {
                        $links[$m['nav_section']][] = [$m['route'], $m['label'], $m['icon']];
                    }
                }
                // Analytics is an add-on, not a module — shown only when the
                // tenant has the analytics_pro add-on active.
                if ($t && $t->hasActiveAddon('analytics_pro')) {
                    $links['Store'][] = ['tenant.analytics', 'Analytics', 'ti-chart-bar'];
                }
                // Theme customizer is always available.
                $links['Content'][] = ['tenant.theme', 'Customise Theme', 'ti-palette'];
// Marketplace / add-ons is always available.
                $links['Settings'][] = ['tenant.addons', 'Marketplace', 'ti-shopping-bag'];
                // Domains & Hosting is always available.
                $links['Settings'][] = ['tenant.infrastructure', 'Domains & Hosting', 'ti-server'];
                // Support tickets are always available.
                $links['Settings'][] = ['tenant.tickets', 'Support', 'ti-ticket'];
            @endphp
            @foreach ($links as $section => $items)
                <div class="eos-nav-section">{{ $section }}</div>
                @foreach ($items as $item)
                    @php
                        [$route, $label, $icon] = $item;
                        $active = request()->routeIs($route);
                    @endphp
                    <a href="{{ Route::has($route) ? route($route) : '#' }}"
                       class="eos-nav-item {{ $active ? 'active' : '' }}">
                        <i class="ti {{ $icon }}"></i> {{ $label }}
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="eos-user">
            <div class="eos-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
            <div style="flex:1;min-width:0;">
                <div class="eos-user-name">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="eos-user-role">{{ $t->name ?? 'Tenant' }}</div>
            </div>
            <form method="POST" action="{{ route('tenant.logout') }}">
                @csrf
                <button type="submit" class="eos-logout" title="Log out"><i class="ti ti-logout" style="font-size:16px;"></i></button>
            </form>
        </div>
    </aside>

    <div class="eos-backdrop" x-show="open" @click="open = false" x-cloak></div>

    <main class="eos-main">
        <div class="eos-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="eos-burger" @click="open = !open"><i class="ti ti-menu-2"></i></button>
                <div>
                    <div class="eos-page-title">@yield('title', 'Dashboard')</div>
                    <div class="eos-page-sub">@yield('subtitle', now()->format('l, j F Y'))</div>
                </div>
            </div>
            <div class="eos-topbar-actions">
                @yield('topbar-action')
            </div>
        </div>

        <div class="eos-content">
            @if (session()->has('impersonator_id'))
                <div class="eos-alert-bar" style="background:rgba(245,158,11,0.12);border-color:#f59e0b;color:#f59e0b;">
                    <i class="ti ti-eye"></i> You are viewing this dashboard as a tenant (impersonation).
                    <form method="POST" action="{{ route('tenant.leave-impersonation') }}" style="display:inline;margin-left:8px;">
                        @csrf
                        <button type="submit" style="background:#f59e0b;color:#fff;border:none;padding:4px 12px;border-radius:6px;cursor:pointer;font-size:11px;font-weight:600;">Leave</button>
                    </form>
                </div>
            @endif
            @if (session('success'))
                <div class="eos-alert-bar"><i class="ti ti-circle-check"></i> {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="eos-alert-bar warn"><i class="ti ti-alert-triangle"></i> {{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="eos-alert-bar warn"><i class="ti ti-alert-triangle"></i> Please fix the errors below.</div>
            @endif
            @yield('content')
        </div>
    </main>

    <nav class="eos-bottom-nav">
        @php
            $bottomLinks = [
                ['tenant.dashboard', 'Dashboard', 'ti-layout-dashboard'],
            ];
            foreach ($modules as $key => $m) {
                if (!is_array($m) || !isset($m['route'])) continue;
                if ($t && $t->hasModule($key)) {
                    $bottomLinks[] = [$m['route'], $m['label'], $m['icon']];
                }
            }
            $bottomLinks[] = ['tenant.addons', 'Marketplace', 'ti-shopping-bag'];
            $bottomLinks[] = ['tenant.tickets', 'Support', 'ti-ticket'];
        @endphp
        @foreach ($bottomLinks as [$route, $label, $icon])
            <a href="{{ Route::has($route) ? route($route) : '#' }}"
               class="{{ request()->routeIs($route) ? 'active' : '' }}">
                <i class="ti {{ $icon }}"></i> {{ $label }}
            </a>
        @endforeach
        <button type="button" @click="open = true"><i class="ti ti-menu-2"></i> Menu</button>
    </nav>
</div>
</body>
</html>
