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
                <div class="eos-logo-sub">Internal CMS</div>
            </div>
        </div>

        <nav class="eos-nav">
            @php
                $u = auth()->user();
                $links = [
                    'Main' => [
                        ['dashboard', 'Dashboard', 'ti-layout-dashboard', null],
                        ['leads.index', 'Leads', 'ti-user-star', \Illuminate\Support\Facades\Schema::hasTable('leads') ? \App\Models\Lead::where('status', 'new')->count() : null],
                        ['clients.index', 'Clients', 'ti-users', \App\Models\Client::count()],
                    ],
                    'Finance' => [
                        ['invoices.index', 'Invoices', 'ti-file-invoice', \App\Models\Invoice::where('status', 'unpaid')->count() ?: null],
                        ['subscriptions.index', 'Subscriptions', 'ti-credit-card', null],
                    ],
                    'Work' => [
                        ['projects.index', 'Projects', 'ti-briefcase', null],
                        ['products.index', 'Products & Services', 'ti-box', null],
                        ['agreements.index', 'Agreements', 'ti-file-text', \App\Models\Agreement::where('status', 'draft')->count() ?: null],
                    ],
                    'Infrastructure' => [
                        ['infrastructure.index', 'Domains & Hosting', 'ti-world', \App\Models\Domain::whereDate('expiry_date', '<=', now()->addDays(30))->count() ?: null],
                        ['tenants.index', 'Tenants', 'ti-building-store', null],
                        ['themes.index', 'Themes', 'ti-palette', null],
                        ['addon-products.index', 'Add-on Pricing', 'ti-tag', null],
                        ['addon-requests.index', 'Add-on Requests', 'ti-shopping-cart', \App\Models\TenantAddon::where('status', 'pending')->count() ?: null],
                    ],
                    'System' => [
                        ['settings.edit', 'Settings', 'ti-settings', null],
                    ],
                ];
            @endphp
            @foreach ($links as $section => $items)
                <div class="eos-nav-section">{{ $section }}</div>
                @foreach ($items as [$route, $label, $icon, $badge])
                    @php
                        $base = explode('.', $route)[0];
                        $active = request()->routeIs($base) || request()->routeIs($base.'.*');
                    @endphp
                    <a href="{{ Route::has($route) ? route($route) : '#' }}"
                       class="eos-nav-item {{ $active ? 'active' : '' }}">
                        <i class="ti {{ $icon }}"></i> {{ $label }}
                        @if ($badge)<span class="eos-nav-badge">{{ $badge }}</span>@endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="eos-user">
            <div class="eos-avatar">{{ strtoupper(substr($u->name ?? 'U', 0, 2)) }}</div>
            <div style="flex:1;min-width:0;">
                <div class="eos-user-name">{{ $u->name ?? 'User' }}</div>
                <div class="eos-user-role">Admin</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
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
                <div class="eos-search"><i class="ti ti-search" style="font-size:14px;"></i> Search anything…</div>

                <div class="eos-bell-wrap" x-data="{ notifOpen: false }">
                    <button type="button" class="eos-bell" @click="notifOpen = !notifOpen" aria-label="Notifications">
                        <i class="ti ti-bell"></i>
                        @if (($notifications['count'] ?? 0) > 0)
                            <span class="eos-bell-badge">{{ $notifications['count'] > 9 ? '9+' : $notifications['count'] }}</span>
                        @endif
                    </button>
                    <div class="eos-notif" x-show="notifOpen" @click.outside="notifOpen = false" x-cloak x-transition>
                        <div class="eos-notif-head">
                            <span>Notifications</span>
                            <span class="eos-notif-count">{{ $notifications['count'] ?? 0 }}</span>
                        </div>
                        @php $groups = [
                            ['Urgent', $notifications['urgent'] ?? []],
                            ['Upcoming', $notifications['upcoming'] ?? []],
                            ['Drafts', $notifications['drafts'] ?? []],
                        ]; @endphp
                        @if (($notifications['count'] ?? 0) === 0)
                            <div class="eos-notif-empty"><i class="ti ti-circle-check"></i> All clear — nothing urgent</div>
                        @else
                            @foreach ($groups as [$gLabel, $gItems])
                                @if (count($gItems))
                                    <div class="eos-notif-group">{{ $gLabel }}</div>
                                    @foreach ($gItems as $item)
                                        <a href="{{ $item['url'] }}" class="eos-notif-item">
                                            <span class="eos-notif-dot dot-{{ $item['level'] }}"></span>
                                            <i class="ti {{ $item['icon'] }}"></i>
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

                <div x-data="{ quickAdd: {{ ($errors->any() && old('quick_add')) ? 'true' : 'false' }} }">
                    <button type="button" class="eos-icon-btn primary" @click="quickAdd = true">
                        <i class="ti ti-plus"></i> New Client
                    </button>

                    <template x-teleport="body">
                        <div class="eos-modal-overlay" x-show="quickAdd" x-cloak
                             @keydown.escape.window="quickAdd = false">
                            <div class="eos-modal" @click.outside="quickAdd = false">
                                <div class="eos-modal-head">
                                    <div>
                                        <div class="eos-modal-title">Quick Add Client</div>
                                        <div class="eos-modal-sub">Just the essentials — add details later</div>
                                    </div>
                                    <button type="button" class="eos-modal-close" @click="quickAdd = false">&times;</button>
                                </div>
                                <form method="POST" action="{{ route('clients.store') }}">
                                    @csrf
                                    <input type="hidden" name="quick_add" value="1">
                                    <div class="eos-modal-body">
                                        <div class="eos-field">
                                            <label class="eos-label">Name *</label>
                                            <input type="text" name="name" value="{{ old('quick_add') ? old('name') : '' }}" class="eos-input" autocomplete="off">
                                            @if (old('quick_add')) @error('name') <div class="eos-error">{{ $message }}</div> @enderror @endif
                                        </div>
                                        <div class="eos-field">
                                            <label class="eos-label">Phone *</label>
                                            <input type="text" name="phone" value="{{ old('quick_add') ? old('phone') : '' }}" class="eos-input" autocomplete="off">
                                            @if (old('quick_add')) @error('phone') <div class="eos-error">{{ $message }}</div> @enderror @endif
                                        </div>
                                        <div class="eos-field">
                                            <label class="eos-label">WhatsApp Number</label>
                                            <input type="text" name="whatsapp" value="{{ old('quick_add') ? old('whatsapp') : '' }}" class="eos-input" placeholder="e.g. 919876543210" autocomplete="off">
                                            @if (old('quick_add')) @error('whatsapp') <div class="eos-error">{{ $message }}</div> @enderror @endif
                                        </div>
                                    </div>
                                    <div class="eos-modal-foot">
                                        <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Create Client</button>
                                        <button type="button" class="eos-btn eos-btn-secondary" @click="quickAdd = false">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>

                @yield('topbar-action')
            </div>
        </div>

        <div class="eos-content">
            @if (session('success') && ! session('new_client_id'))
                <div class="eos-alert-bar"><i class="ti ti-circle-check"></i> {{ session('success') }}</div>
            @endif
            @if (session('new_client_id'))
                <div class="eos-toast" x-data="{ show: true }" x-show="show" x-cloak
                     x-init="setTimeout(() => show = false, 9000)" x-transition>
                    <i class="ti ti-circle-check eos-toast-icon"></i>
                    <div>
                        <div class="eos-toast-text">Client created. Add more details anytime.</div>
                        <a href="{{ route('clients.show', session('new_client_id')) }}" class="eos-toast-link">View Profile →</a>
                    </div>
                    <button type="button" class="eos-toast-close" @click="show = false">&times;</button>
                </div>
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
                ['dashboard', 'Dashboard', 'ti-layout-dashboard'],
                ['leads.index', 'Leads', 'ti-user-star'],
                ['clients.index', 'Clients', 'ti-users'],
                ['projects.index', 'Projects', 'ti-briefcase'],
                ['invoices.index', 'Invoices', 'ti-file-invoice'],
                ['tenants.index', 'Tenants', 'ti-building-store'],
            ];
        @endphp
        @foreach ($bottomLinks as [$route, $label, $icon])
            @php $base = explode('.', $route)[0]; @endphp
            <a href="{{ Route::has($route) ? route($route) : '#' }}"
               class="{{ request()->routeIs($base) || request()->routeIs($base.'.*') ? 'active' : '' }}">
                <i class="ti {{ $icon }}"></i> {{ $label }}
            </a>
        @endforeach
        <button type="button" @click="open = true"><i class="ti ti-menu-2"></i> Menu</button>
    </nav>
</div>
</body>
</html>
