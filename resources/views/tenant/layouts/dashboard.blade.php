<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Ehlom OS') }}</title>
    @php $dashboardTenant = app(\App\Services\TenantContext::class)->get(); @endphp
    @if (($dashboardTenant?->theme_settings['favicon'] ?? null))
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::url($dashboardTenant->theme_settings['favicon']) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body.store-admin-theme {
            --bg-base: #f5f7fb;
            --bg-sidebar: #122033;
            --bg-card: #ffffff;
            --bg-hover: #eef4ff;
            --bg-active: #e8f1ff;
            --bg-input: #ffffff;
            --border: #dbe3f0;
            --border-card: #dfe7f2;
            --text-primary: #172033;
            --text-secondary: #3f4c63;
            --text-muted: #738096;
            --text-dim: #8b97aa;
            --text-dimmer: #9ca8ba;
            --accent-blue: #2563eb;
            --accent-purple: #7c3aed;
            --accent-teal: #059669;
            --accent-amber: #f59e0b;
            --accent-red: #dc2626;
            --accent-green: #16a34a;
            background:
                linear-gradient(180deg, #f7f9fd 0%, #eef3f9 100%);
            color: var(--text-primary);
        }
        body.store-admin-theme .eos-sidebar {
            background:
                linear-gradient(180deg, #162742 0%, #101b2d 100%),
                #122033;
            border-right-color: rgba(255,255,255,.08);
            box-shadow: 14px 0 40px rgba(15,23,42,.08);
        }
        body.store-admin-theme .eos-logo-text,
        body.store-admin-theme .eos-user-name { color: #f8fafc; }
        body.store-admin-theme .eos-logo-sub,
        body.store-admin-theme .eos-user-role,
        body.store-admin-theme .eos-nav-section { color: #7f8da8; }
        body.store-admin-theme .eos-nav-item { color: #b6c2d6; }
        body.store-admin-theme .eos-nav-item:hover { background: rgba(59,130,246,.12); color: #f8fafc; }
        body.store-admin-theme .eos-nav-item.active {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            box-shadow: 0 10px 24px rgba(37,99,235,.18);
        }
        body.store-admin-theme .store-nav-group[open] > summary {
            background: rgba(59,130,246,.1);
            color: #f8fafc;
        }
        body.store-admin-theme .store-nav-group.active > summary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            box-shadow: 0 10px 24px rgba(37,99,235,.18);
        }
        body.store-admin-theme .store-nav-child.active {
            color: #dbeafe;
            background: rgba(37,99,235,.16);
            border-color: rgba(96,165,250,.3);
        }
        body.store-admin-theme .eos-user { border-top-color: rgba(255,255,255,.08); }
        body.store-admin-theme .eos-main { background: transparent; }
        body.store-admin-theme .eos-topbar {
            background: rgba(245,247,251,.88);
            border-bottom-color: rgba(219,227,240,.86);
        }
        body.store-admin-theme .eos-page-title { color: #111827; }
        body.store-admin-theme .eos-page-sub { color: #667085; }
        body.store-admin-theme .eos-card,
        body.store-admin-theme .eos-stat,
        body.store-admin-theme .eos-modal,
        body.store-admin-theme .eos-notif {
            background: #fff;
            border-color: var(--border-card);
            box-shadow: 0 14px 34px rgba(15,23,42,.06);
        }
        body.store-admin-theme .eos-stat:hover {
            border-color: #bfdbfe;
            box-shadow: 0 18px 42px rgba(15,23,42,.1);
        }
        body.store-admin-theme .eos-input,
        body.store-admin-theme select.eos-input,
        body.store-admin-theme textarea.eos-input {
            background: #fff;
            color: var(--text-primary);
            border-color: #d9e2ef;
        }
        body.store-admin-theme .eos-list-item {
            border-bottom-color: #edf1f7;
        }
        body.store-admin-theme .eos-bottom-nav {
            background: #fff;
            border-top-color: #e5edf7;
            box-shadow: 0 -12px 30px rgba(15,23,42,.08);
        }
        @media (max-width: 980px) {
            body.store-admin-theme .eos-bottom-nav {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 0;
                padding: 7px 4px calc(7px + env(safe-area-inset-bottom, 0px));
            }
            body.store-admin-theme .eos-bottom-nav a,
            body.store-admin-theme .eos-bottom-nav button {
                min-width: 0;
                min-height: 54px;
                justify-content: center;
                gap: 3px;
                padding: 4px 2px;
                color: #64748b;
                font-size: 10.5px;
                line-height: 1.1;
                text-align: center;
            }
            body.store-admin-theme .eos-bottom-nav .ti {
                font-size: 22px;
                line-height: 1;
            }
            body.store-admin-theme .eos-bottom-nav a.active {
                color: #2563eb;
            }
        }
        body.store-admin-theme .store-console-chip {
            background: #fff;
            border-color: #d9e2ef;
            color: #475569;
        }
        @media (min-width: 760px) {
            .eos-sidebar {
                width: 270px;
                min-width: 270px;
                transform: none !important;
                background:
                    linear-gradient(180deg, rgba(24, 29, 45, .95), rgba(11, 13, 21, .98)),
                    var(--bg-sidebar);
            }
            .eos-main { margin-left: 270px; }
            .eos-topbar {
                position: sticky;
                top: 0;
                z-index: 25;
                padding: 20px 28px 14px;
                background: rgba(18, 20, 29, .88);
                border-bottom: 1px solid rgba(42, 48, 71, .72);
                backdrop-filter: blur(14px);
            }
            .eos-content { padding: 22px 28px 32px; }
            .eos-bottom-nav { display: none !important; }
            .eos-logo { padding: 26px 20px 18px; }
            .eos-nav { padding: 6px 14px 16px; }
            .eos-nav-item { padding: 10px 12px; font-size: 13px; border-radius: 9px; }
            .eos-nav-section { padding-top: 18px; }
            .eos-burger { display: none !important; }
            .eos-backdrop { display: none !important; }
        }
        @media (min-width: 760px) and (max-width: 980px) {
            .eos-sidebar { box-shadow: none !important; }
            .eos-main { margin-left: 270px !important; }
            .eos-topbar {
                padding: 18px 22px 12px !important;
                background: rgba(18, 20, 29, .88) !important;
            }
            .eos-content {
                padding: 18px 22px 28px !important;
                padding-bottom: 28px !important;
            }
        }
        .store-logo-image {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid rgba(255,255,255,.08);
            background: #11141f;
        }
        .store-console-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border: 1px solid var(--border);
            border-radius: 999px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            background: rgba(24, 28, 43, .72);
        }
        .store-console-chip:hover { color: var(--text-primary); border-color: #33406b; }
        .store-nav-group {
            margin-bottom: 4px;
        }
        .store-nav-group summary {
            list-style: none;
        }
        .store-nav-group summary::-webkit-details-marker {
            display: none;
        }
        .store-nav-summary {
            justify-content: space-between;
        }
        .store-nav-summary-left {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }
        .store-nav-chevron {
            margin-left: auto;
            font-size: 14px !important;
            transition: transform .16s ease;
        }
        .store-nav-group[open] .store-nav-chevron {
            transform: rotate(90deg);
        }
        .store-nav-children {
            display: grid;
            gap: 3px;
            margin: 5px 0 9px 28px;
            padding-left: 10px;
            border-left: 1px solid rgba(96,165,250,.2);
        }
        .store-nav-child {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 30px;
            padding: 7px 10px;
            border: 1px solid transparent;
            border-radius: 7px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
        }
        .store-nav-child:hover {
            background: rgba(37,99,235,.1);
            border-color: rgba(96,165,250,.18);
            color: #e2e8f0;
        }
        .store-nav-child .ti {
            font-size: 15px;
        }
        .store-nav-saas {
            margin-top: 16px;
            padding: 12px 8px 0;
            border-top: 1px solid rgba(148,163,184,.18);
            position: relative;
        }
        .store-nav-saas::before {
            content: '';
            position: absolute;
            left: 10px;
            right: 10px;
            top: -1px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(96,165,250,.55), transparent);
        }
        .store-nav-hub-label {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 8px 9px;
            color: #bfdbfe;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 1.35px;
            text-transform: uppercase;
        }
        .store-nav-hub-label i {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #fff;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            font-size: 14px;
        }
        .store-module-shell { display: grid; gap: 16px; }
        .store-module-hero {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 18px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(15,23,42,.06);
            overflow: hidden;
        }
        .store-module-hero::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: linear-gradient(90deg, #059669, #2563eb, #f59e0b);
        }
        .store-module-kicker {
            color: var(--accent-teal);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }
        .store-module-title {
            color: var(--text-primary);
            font-family: 'Syne', sans-serif;
            font-size: 23px;
            font-weight: 700;
            margin-top: 6px;
        }
        .store-module-copy {
            color: var(--text-muted);
            font-size: 12.5px;
            line-height: 1.6;
            margin-top: 6px;
            max-width: 720px;
        }
        .store-module-stats {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .store-mini-stat {
            min-width: 110px;
            padding: 11px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #f8fafc;
        }
        .store-mini-stat strong { display: block; color: var(--text-primary); font-size: 19px; line-height: 1; }
        .store-mini-stat span { display: block; color: var(--text-muted); font-size: 10px; margin-top: 6px; }
        .store-panel-clean {
            border: 1px solid var(--border-card);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(15,23,42,.06);
            overflow: hidden;
        }
        .storefront-panel {
            position: relative;
            padding: 24px;
            overflow: visible;
        }
        .storefront-panel::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            border-radius: 12px 12px 0 0;
            background: linear-gradient(90deg, #2563eb, #059669, #f59e0b);
        }
        .storefront-panel-title {
            color: var(--text-primary);
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            line-height: 1.18;
            font-weight: 900;
            margin-bottom: 6px;
        }
        .storefront-panel-sub {
            max-width: 760px;
            color: var(--text-muted);
            font-size: 13.5px;
            line-height: 1.55;
            margin-bottom: 20px;
        }
        body.store-admin-theme .storefront-panel .eos-field {
            margin-bottom: 16px;
            padding: 12px;
            border: 1px solid #e5edf7;
            border-radius: 12px;
            background: linear-gradient(180deg, #fbfdff 0%, #f8fafc 100%);
        }
        body.store-admin-theme .eos-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1px;
        }
        body.store-admin-theme .storefront-panel .eos-input,
        body.store-admin-theme .storefront-panel select.eos-input,
        body.store-admin-theme .storefront-panel textarea.eos-input {
            min-height: 46px;
            padding: 12px 13px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.45;
        }
        body.store-admin-theme .storefront-panel textarea.eos-input {
            min-height: 170px;
            resize: vertical;
        }
        body.store-admin-theme .storefront-panel .eos-input:focus,
        body.store-admin-theme .storefront-panel select.eos-input:focus,
        body.store-admin-theme .storefront-panel textarea.eos-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,.1);
        }
        .store-panel-clean-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 15px 16px;
            border-bottom: 1px solid #eef2f7;
        }
        .store-panel-clean-title { color: var(--text-primary); font-size: 13px; font-weight: 900; }
        .store-panel-clean-sub { color: var(--text-muted); font-size: 11px; margin-top: 3px; }
        .store-record-row {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f7;
        }
        .store-record-row:last-child { border-bottom: 0; }
        .store-record-thumb {
            width: 54px;
            height: 54px;
            border-radius: 10px;
            overflow: hidden;
            background: var(--bg-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dim);
            flex-shrink: 0;
        }
        .store-record-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .store-record-name { color: var(--text-primary); font-size: 13px; font-weight: 900; }
        .store-record-meta { color: var(--text-muted); font-size: 11.5px; line-height: 1.45; margin-top: 4px; }
        .store-record-actions { display: flex; align-items: center; gap: 6px; margin-left: auto; }
        .store-empty-state {
            display: grid;
            place-items: center;
            min-height: 230px;
            padding: 28px 16px;
            text-align: center;
            color: var(--text-muted);
        }
        .store-empty-state i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            margin-bottom: 12px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
            box-shadow: 0 14px 28px rgba(37,99,235,.18);
            font-size: 23px;
        }
        .store-empty-title { color: var(--text-primary); font-size: 15px; font-weight: 900; }
        .store-empty-copy { max-width: 420px; margin-top: 6px; font-size: 12px; line-height: 1.6; }
        @media (max-width: 759px) {
            html,
            body.store-admin-theme {
                overflow-x: hidden;
            }
            body.store-admin-theme .eos-wrap,
            body.store-admin-theme .eos-main,
            body.store-admin-theme .eos-topbar,
            body.store-admin-theme .eos-content {
                width: 100%;
                min-width: 0;
                max-width: 100vw;
            }
            body.store-admin-theme .eos-main {
                flex-basis: 100%;
            }
            body.store-admin-theme .eos-topbar > div:first-child,
            body.store-admin-theme .eos-topbar > div:first-child > div,
            body.store-admin-theme .eos-content > * {
                min-width: 0;
                max-width: 100%;
            }
            body.store-admin-theme .eos-page-sub {
                white-space: normal;
                line-height: 1.35;
            }
            body.store-admin-theme .eos-topbar-actions {
                flex: 0 0 auto;
            }
            body.store-admin-theme .store-console-chip {
                width: 36px;
                height: 36px;
                justify-content: center;
                padding: 0;
                font-size: 0;
            }
            body.store-admin-theme .store-console-chip .ti {
                font-size: 16px;
            }
            body.store-admin-theme .eos-alert-bar {
                min-width: 0;
                flex-wrap: wrap;
                padding: 10px 12px;
                line-height: 1.4;
            }
            body.store-admin-theme .eos-alert-bar form {
                margin-left: 0 !important;
            }
            .store-module-hero { align-items: flex-start; flex-direction: column; }
            .store-module-stats { width: 100%; justify-content: stretch; }
            .store-mini-stat { flex: 1; min-width: 0; }
            .store-record-row { align-items: flex-start; }
            .store-record-actions { align-self: center; }
            .storefront-panel { padding: 18px; }
            .storefront-panel-title { font-size: 20px; }
            .storefront-panel-sub { font-size: 13px; }
        }
    </style>
</head>
<body class="antialiased store-admin-theme">
<div class="eos-wrap" x-data="{ open: false }">
    @php
        $t = $dashboardTenant;
        $staticMode = ($t?->site_mode ?? 'managed') === 'static';
    @endphp

    <aside class="eos-sidebar" :class="{ 'open': open }">
        <div class="eos-logo">
            @if (!empty($t?->logo))
                <img src="{{ \Illuminate\Support\Facades\Storage::url($t->logo) }}" alt="{{ $t->name ?? 'Store' }}" class="store-logo-image">
            @else
                <div class="eos-logo-icon">{{ strtoupper(substr($t->name ?? 'S', 0, 1)) }}</div>
            @endif
            <div>
                <div class="eos-logo-text">{{ $t->name ?? 'Store' }}</div>
                <div class="eos-logo-sub">{{ $staticMode ? 'Website Account' : (($t?->site_type ?? null) === 'business' ? 'Business Console' : 'Store Console') }}</div>
            </div>
        </div>

        <nav class="eos-nav">
            @php
                $modules = config('modules');
                $can = fn (?string $key = null) => !$key || ($t && $t->hasModule($key));
                $hrefFor = fn (array $item) => isset($item['url'])
                    ? $item['url']
                    : (Route::has($item['route'])
                    ? (($item['route'] === 'tenant.commerce-feature') ? route($item['route'], $item['feature'] ?? null) : route($item['route'], $item['params'] ?? []))
                    : '#');
                $activeFor = fn (array $item) => isset($item['active_type'])
                    ? request()->routeIs('tenant.business-content.*') && request()->route('type') === $item['active_type']
                    : (isset($item['active_tab'])
                    ? request()->routeIs($item['route']) && request()->query('tab') === $item['active_tab']
                    : (isset($item['active_without_tab'])
                    ? request()->routeIs($item['route']) && !request()->has('tab')
                    : (isset($item['active'])
                    ? request()->routeIs($item['active'])
                    : (($item['route'] === 'tenant.commerce-feature')
                    ? request()->routeIs($item['route']) && request()->route('feature') === ($item['feature'] ?? null)
                    : request()->routeIs($item['route'])))));

                $groups = [
                    [
                        'label' => 'Orders',
                        'icon' => 'ti-package',
                        'items' => array_values(array_filter([
                            $can('orders') ? ['route' => 'tenant.orders', 'label' => 'All Orders', 'icon' => 'ti-list-details'] : null,
                            $can('shipping_rules') ? ['route' => 'tenant.shipping-rules', 'label' => 'Shipping Rules', 'icon' => 'ti-truck-delivery'] : null,
                            $can('abandoned_cart') ? ['route' => 'tenant.commerce-feature', 'feature' => 'abandoned_cart', 'label' => 'Abandoned Carts', 'icon' => 'ti-shopping-cart-exclamation'] : null,
                        ])),
                    ],
                    [
                        'label' => 'Products',
                        'icon' => 'ti-box',
                        'items' => array_values(array_filter([
                            $can('catalog') ? ['route' => 'tenant.catalog', 'label' => 'All Products', 'icon' => 'ti-box'] : null,
                            $can('catalog') ? ['route' => 'tenant.catalog.create', 'label' => 'Add Product', 'icon' => 'ti-plus'] : null,
                            $can('product_collections') ? ['route' => 'tenant.collections', 'label' => 'Collections', 'icon' => 'ti-folders'] : null,
                            $can('inventory') ? ['route' => 'tenant.inventory', 'label' => 'Inventory', 'icon' => 'ti-packages'] : null,
                            $can('variants') ? ['route' => 'tenant.attributes', 'label' => 'Options / Attributes', 'icon' => 'ti-adjustments'] : null,
                            $can('bulk_import_export') ? ['route' => 'tenant.commerce-feature', 'feature' => 'bulk_import_export', 'label' => 'Bulk Import / Export', 'icon' => 'ti-file-spreadsheet'] : null,
                        ])),
                    ],
                    [
                        'label' => 'Customers',
                        'icon' => 'ti-users',
                        'items' => array_values(array_filter([
                            $can('customer_accounts') ? ['route' => 'tenant.customers', 'label' => 'All Customers', 'icon' => 'ti-users'] : null,
                            $can('reviews') ? ['route' => 'tenant.reviews', 'label' => 'Reviews', 'icon' => 'ti-star'] : null,
                            $can('loyalty_rewards') ? ['route' => 'tenant.commerce-feature', 'feature' => 'loyalty_rewards', 'label' => 'Loyalty Points', 'icon' => 'ti-gift'] : null,
                        ])),
                    ],
                    [
                        'label' => 'Marketing',
                        'icon' => 'ti-speakerphone',
                        'items' => array_values(array_filter([
                            $can('marketing_sections') ? ['route' => 'tenant.marketing-sections', 'label' => 'Homepage Sections', 'icon' => 'ti-layout-grid-add'] : null,
                            $can('coupons') ? ['route' => 'tenant.coupons', 'label' => 'Coupons / Discounts', 'icon' => 'ti-ticket'] : null,
                            $can('advanced_analytics') ? ['route' => 'tenant.commerce-feature', 'feature' => 'advanced_analytics', 'label' => 'Analytics', 'icon' => 'ti-chart-histogram'] : null,
                        ])),
                    ],
                    [
                        'label' => 'Payments',
                        'icon' => 'ti-credit-card',
                        'items' => array_values(array_filter([
                            $can('payments') ? ['route' => 'tenant.payments', 'label' => 'Payment Methods', 'icon' => 'ti-credit-card'] : null,
                            $can('gst_invoice') ? ['route' => 'tenant.commerce-feature', 'feature' => 'gst_invoice', 'label' => 'GST / Tax', 'icon' => 'ti-file-invoice'] : null,
                            $can('pos_integration') ? ['route' => 'tenant.commerce-feature', 'feature' => 'pos_integration', 'label' => 'POS Export', 'icon' => 'ti-device-desktop'] : null,
                        ])),
                    ],
                ];

                // Portfolio / Business is a service workspace, not a shop.
                // Its enabled modules must appear in its own information
                // architecture instead of being hidden behind shopping groups.
                if (($t?->site_type ?? null) === 'business') {
                    $groups = [
                        [
                            'label' => 'Business Content',
                            'icon' => 'ti-briefcase-2',
                            'items' => array_values(array_filter([
                                $can('services') ? ['route' => 'tenant.services', 'label' => 'Services', 'icon' => 'ti-briefcase-2'] : null,
                                $can('case_studies') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'case-studies'], 'active_type' => 'case-studies', 'label' => 'Case Studies', 'icon' => 'ti-file-text'] : null,
                                $can('testimonials') ? ['route' => 'tenant.testimonials', 'label' => 'Testimonials', 'icon' => 'ti-quote'] : null,
                                $can('team') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'team'], 'active_type' => 'team', 'label' => 'Team', 'icon' => 'ti-users'] : null,
                                $can('blog') ? ['route' => 'tenant.blog', 'label' => 'Blog', 'icon' => 'ti-news'] : null,
                                $can('careers') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'careers'], 'active_type' => 'careers', 'label' => 'Careers', 'icon' => 'ti-id-badge-2'] : null,
                                $can('enquiries') ? ['route' => 'tenant.business-inbox', 'label' => 'Enquiries', 'icon' => 'ti-inbox'] : null,
                                $can('newsletter') ? ['route' => 'tenant.business-inbox', 'params' => ['tab' => 'subscribers'], 'active_tab' => 'subscribers', 'label' => 'Subscribers', 'icon' => 'ti-mail'] : null,
                                $can('content') ? ['route' => 'tenant.content', 'label' => 'Business Details', 'icon' => 'ti-building'] : null,
                            ])),
                        ],
                        [
                            'label' => 'Website',
                            'icon' => 'ti-world',
                            'items' => array_values(array_filter([
                                ['route' => 'tenant.theme', 'label' => 'Website Editor', 'icon' => 'ti-browser'],
                                ['route' => 'tenant.custom-pages', 'label' => 'Custom Pages', 'icon' => 'ti-file-plus'],
                                ['route' => 'tenant.seo', 'label' => 'SEO', 'icon' => 'ti-search'],
                            ])),
                        ],
                    ];
                }

                // School is a public-school website console. Keep its
                // information architecture separate from both Store CRM and
                // Portfolio/Business workspaces.
                if (($t?->site_type ?? null) === 'school') {
                    $groups = [
                        [
                            'label' => 'Website Content',
                            'icon' => 'ti-world',
                            'items' => array_values(array_filter([
                                $can('content') ? ['route' => 'tenant.content', 'label' => 'School Profile & Gallery', 'icon' => 'ti-building'] : null,
                                ['route' => 'tenant.theme', 'label' => 'Homepage Editor', 'icon' => 'ti-browser', 'active_without_tab' => true],
                                ['route' => 'tenant.theme', 'url' => route('tenant.theme') . '?tab=about', 'active_tab' => 'about', 'label' => 'About & Principal', 'icon' => 'ti-message-circle'],
                                ['route' => 'tenant.theme', 'url' => route('tenant.theme') . '?tab=contact', 'active_tab' => 'contact', 'label' => 'Contact & Map', 'icon' => 'ti-map-pin'],
                                ['route' => 'tenant.custom-pages', 'label' => 'Custom Pages', 'icon' => 'ti-file-plus'],
                                ['route' => 'tenant.seo', 'label' => 'SEO & Sharing', 'icon' => 'ti-search'],
                                ['route' => 'tenant.theme', 'url' => route('tenant.theme') . '?tab=policies', 'active_tab' => 'policies', 'label' => 'Policies', 'icon' => 'ti-file-description'],
                            ])),
                        ],
                        [
                            'label' => 'Academics & People',
                            'icon' => 'ti-school',
                            'items' => array_values(array_filter([
                                $can('academics') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'academics'], 'active_type' => 'academics', 'label' => 'Academics & Classes', 'icon' => 'ti-book'] : null,
                                $can('faculty') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'faculty'], 'active_type' => 'faculty', 'label' => 'Faculty & Staff', 'icon' => 'ti-users'] : null,
                                $can('facilities') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'facilities'], 'active_type' => 'facilities', 'label' => 'Campus & Facilities', 'icon' => 'ti-building-community'] : null,
                                $can('student_life') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'activities'], 'active_type' => 'activities', 'label' => 'Student Life', 'icon' => 'ti-ball-football'] : null,
                            ])),
                        ],
                        [
                            'label' => 'Admissions & Updates',
                            'icon' => 'ti-speakerphone',
                            'items' => array_values(array_filter([
                                $can('admissions') ? ['route' => 'tenant.theme', 'url' => route('tenant.theme') . '?tab=admissions', 'active_tab' => 'admissions', 'label' => 'Admission Information', 'icon' => 'ti-clipboard'] : null,
                                $can('enquiry_form') ? ['route' => 'tenant.business-inbox', 'label' => 'Admission Enquiries', 'icon' => 'ti-inbox'] : null,
                                $can('news') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'notices'], 'active_type' => 'notices', 'label' => 'News & Notices', 'icon' => 'ti-news'] : null,
                                $can('achievements') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'achievements'], 'active_type' => 'achievements', 'label' => 'Achievements', 'icon' => 'ti-trophy'] : null,
                                $can('testimonials') ? ['route' => 'tenant.theme', 'url' => route('tenant.theme') . '?tab=testimonials', 'active_tab' => 'testimonials', 'label' => 'Testimonials', 'icon' => 'ti-quote'] : null,
                                $can('downloads') ? ['route' => 'tenant.theme', 'url' => route('tenant.theme') . '?tab=downloads', 'active_tab' => 'downloads', 'label' => 'Downloads', 'icon' => 'ti-file-download'] : null,
                                $can('certificates') ? ['route' => 'tenant.theme', 'url' => route('tenant.theme') . '?tab=certificates', 'active_tab' => 'certificates', 'label' => 'Certificates', 'icon' => 'ti-certificate'] : null,
                            ])),
                        ],
                    ];
                }

                if ($staticMode) {
                    $groups = [];
                }
            @endphp

            <div class="eos-nav-section">{{ $staticMode ? 'Client Account' : (($t?->site_type ?? null) === 'business' ? 'Business Console' : (($t?->site_type ?? null) === 'school' ? 'School Console' : 'Store CRM')) }}</div>
            <a href="{{ route('tenant.dashboard') }}" class="eos-nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                <i class="ti ti-layout-dashboard"></i> Dashboard
            </a>

            @if ($staticMode)
                <a href="{{ url('/') }}" class="eos-nav-item" target="_blank" rel="noopener">
                    <i class="ti ti-world"></i> View Website
                </a>
                <a href="{{ route('tenant.infrastructure') }}" class="eos-nav-item {{ request()->routeIs('tenant.infrastructure') ? 'active' : '' }}">
                    <i class="ti ti-receipt"></i> Services & Billing
                </a>
                <a href="{{ route('tenant.tickets') }}" class="eos-nav-item {{ request()->routeIs('tenant.tickets*') ? 'active' : '' }}">
                    <i class="ti ti-lifebuoy"></i> Support
                </a>
            @endif

            @foreach ($groups as $group)
                @continue(empty($group['items']))
                @php $groupActive = collect($group['items'])->contains(fn ($item) => $activeFor($item)); @endphp
                <details class="store-nav-group {{ $groupActive ? 'active' : '' }}" @if($groupActive) open @endif>
                    <summary class="eos-nav-item store-nav-summary">
                        <span class="store-nav-summary-left"><i class="ti {{ $group['icon'] }}"></i> {{ $group['label'] }}</span>
                        <i class="ti ti-chevron-right store-nav-chevron"></i>
                    </summary>
                    <div class="store-nav-children">
                        @foreach ($group['items'] as $item)
                            @php $active = $activeFor($item); @endphp
                            <a href="{{ $hrefFor($item) }}" class="store-nav-child {{ $active ? 'active' : '' }}">
                                <i class="ti {{ $item['icon'] }}"></i> {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </details>
            @endforeach

            @if (!$staticMode && !in_array(($t?->site_type ?? null), ['business', 'school'], true))
            <a href="{{ route('tenant.theme') }}" class="eos-nav-item {{ request()->routeIs('tenant.theme') && !request()->has('tab') ? 'active' : '' }}">
                <i class="ti ti-browser"></i> Storefront Editor
            </a>
            <a href="{{ route('tenant.theme') }}?tab=policies" class="eos-nav-item {{ request()->routeIs('tenant.theme') && request()->query('tab') === 'policies' ? 'active' : '' }}">
                <i class="ti ti-file-description"></i> Terms & Policies
            </a>
            <a href="{{ route('tenant.custom-pages') }}" class="eos-nav-item {{ request()->routeIs('tenant.custom-pages*') ? 'active' : '' }}">
                <i class="ti ti-file-plus"></i> Custom Pages
            </a>
            <a href="{{ route('tenant.settings') }}" class="eos-nav-item {{ request()->routeIs('tenant.settings') || request()->routeIs('tenant.seo') ? 'active' : '' }}">
                <i class="ti ti-settings"></i> Settings
            </a>
            @endif

            @if (!$staticMode)
            <div class="store-nav-saas">
                <div class="store-nav-hub-label"><i class="ti ti-sparkles"></i> Ehlom Hub</div>
                <a href="{{ route('tenant.addons') }}" class="eos-nav-item {{ request()->routeIs('tenant.addons') ? 'active' : '' }}">
                    <i class="ti ti-shopping-bag"></i> Add-on Marketplace
                </a>
                <a href="{{ route('tenant.infrastructure') }}" class="eos-nav-item {{ request()->routeIs('tenant.infrastructure') ? 'active' : '' }}">
                    <i class="ti ti-server"></i> Domains & Hosting
                </a>
                <a href="{{ route('tenant.tickets') }}" class="eos-nav-item {{ request()->routeIs('tenant.tickets') ? 'active' : '' }}">
                    <i class="ti ti-ticket"></i> Support
                </a>
            </div>
            @endif
        </nav>

        <div class="eos-user">
            <div class="eos-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
            <div style="flex:1;min-width:0;">
                <div class="eos-user-name">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="eos-user-role">{{ $staticMode ? 'Website Client' : 'Store Owner' }}</div>
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
                <a href="{{ url('/') }}" class="store-console-chip" target="_blank" rel="noopener">
                    <i class="ti ti-external-link"></i> View Store
                </a>
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

    <style>
        body.store-admin-theme .store-command,
        body.store-admin-theme .store-panel,
        body.store-admin-theme .store-kpi,
        body.store-admin-theme .store-action,
        body.store-admin-theme .addon-shop-hero,
        body.store-admin-theme .addon-card {
            background: #fff !important;
            border-color: var(--border-card) !important;
            box-shadow: 0 14px 34px rgba(15,23,42,.06);
        }
        body.store-admin-theme .store-command::before {
            background: linear-gradient(90deg, #059669, #2563eb, #f59e0b) !important;
        }
        body.store-admin-theme .addon-card::before {
            background: linear-gradient(90deg, #f59e0b, #2563eb) !important;
        }
        body.store-admin-theme .addon-card.active::before { background: #059669 !important; }
        body.store-admin-theme .addon-card.pending::before { background: #f59e0b !important; }
        body.store-admin-theme .store-title,
        body.store-admin-theme .store-panel-title,
        body.store-admin-theme .store-kpi-value,
        body.store-admin-theme .store-action-name,
        body.store-admin-theme .store-order-id,
        body.store-admin-theme .store-order-total,
        body.store-admin-theme .store-check-row,
        body.store-admin-theme .addon-shop-title,
        body.store-admin-theme .addon-card-name,
        body.store-admin-theme .addon-price-main,
        body.store-admin-theme .eos-row-name,
        body.store-admin-theme .eos-amt {
            color: var(--text-primary) !important;
        }
        body.store-admin-theme .store-copy,
        body.store-admin-theme .store-panel-sub,
        body.store-admin-theme .store-kpi-label,
        body.store-admin-theme .store-action-text,
        body.store-admin-theme .store-order-meta,
        body.store-admin-theme .store-empty,
        body.store-admin-theme .addon-shop-copy,
        body.store-admin-theme .addon-card-desc,
        body.store-admin-theme .addon-price-sub,
        body.store-admin-theme .eos-row-type,
        body.store-admin-theme .eos-card-title {
            color: var(--text-muted) !important;
        }
        body.store-admin-theme .store-side-summary,
        body.store-admin-theme .store-summary-row,
        body.store-admin-theme .store-check-row,
        body.store-admin-theme .store-order-row {
            border-color: #eef2f7 !important;
        }
        body.store-admin-theme .store-progress { background: #e8eef7 !important; }
        body.store-admin-theme .addon-shop-metric,
        body.store-admin-theme .store-mini-stat {
            background: #f8fafc !important;
            border-color: #e5edf7 !important;
        }
        body.store-admin-theme .eos-alert-bar {
            background: #ecfdf5;
            border-color: #bbf7d0;
            color: #047857;
        }
        body.store-admin-theme .eos-alert-bar.warn {
            background: #fffbeb;
            border-color: #fde68a;
            color: #b45309;
        }
        body.store-admin-theme .eos-btn-primary,
        body.store-admin-theme .store-primary-btn,
        body.store-admin-theme .addon-cta {
            background: #2563eb !important;
            color: #fff !important;
            box-shadow: 0 10px 22px rgba(37,99,235,.22);
        }
        body.store-admin-theme .eos-btn-secondary,
        body.store-admin-theme .store-secondary-btn,
        body.store-admin-theme .addon-cta.secondary {
            background: #fff !important;
            color: #334155 !important;
            border: 1px solid #d9e2ef !important;
            box-shadow: none;
        }
        body.store-admin-theme .eos-empty,
        body.store-admin-theme .store-empty-state {
            color: var(--text-muted);
        }
        body.store-admin-theme .store-record-thumb,
        body.store-admin-theme .eos-init {
            background: #eef4ff !important;
            color: #2563eb !important;
        }
        body.store-admin-theme .eos-card:has(form) {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(37,99,235,.1), transparent 34%),
                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%) !important;
            border-color: #d9e5f4 !important;
            box-shadow: 0 18px 46px rgba(15,23,42,.08) !important;
        }
        body.store-admin-theme .eos-card:has(form)::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 5px;
            background: linear-gradient(90deg, #2563eb, #059669, #f59e0b);
            z-index: 1;
        }
        body.store-admin-theme .eos-card:has(form) .eos-card-header {
            position: relative;
            padding: 20px 22px 16px;
            border-bottom: 1px solid #e5edf7;
            background:
                linear-gradient(135deg, rgba(37,99,235,.08), rgba(5,150,105,.06)),
                rgba(255,255,255,.72);
        }
        body.store-admin-theme .eos-card:has(form) .eos-card-title {
            color: #111827 !important;
            font-family: 'Syne', sans-serif;
            font-size: 21px;
            line-height: 1.2;
            font-weight: 900;
        }
        body.store-admin-theme .eos-card:has(form) form {
            padding: 20px 22px 22px !important;
        }
        body.store-admin-theme .eos-card:has(form) .eos-field {
            margin-bottom: 15px;
            padding: 12px;
            border: 1px solid #e1eaf6;
            border-radius: 12px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,250,252,.96));
            box-shadow: inset 0 1px 0 rgba(255,255,255,.82);
        }
        body.store-admin-theme .eos-card:has(form) .eos-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1px;
        }
        body.store-admin-theme .eos-card:has(form) .eos-input,
        body.store-admin-theme .eos-card:has(form) select.eos-input,
        body.store-admin-theme .eos-card:has(form) textarea.eos-input {
            min-height: 46px;
            padding: 12px 13px;
            border-radius: 10px;
            border-color: #d7e2f0;
            background: #fff;
            color: #172033;
            font-size: 14px;
            line-height: 1.45;
        }
        body.store-admin-theme .eos-card:has(form) textarea.eos-input {
            min-height: 138px;
        }
        body.store-admin-theme .eos-card:has(form) .eos-input:focus,
        body.store-admin-theme .eos-card:has(form) select.eos-input:focus,
        body.store-admin-theme .eos-card:has(form) textarea.eos-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,.1);
        }
        body.store-admin-theme .eos-card:has(form) .eos-row-type {
            color: #64748b !important;
            font-size: 12px;
            line-height: 1.5;
        }
        @media (max-width: 759px) {
            body.store-admin-theme .eos-card:has(form) .eos-card-header {
                padding: 18px 18px 14px;
            }
            body.store-admin-theme .eos-card:has(form) form {
                padding: 16px 18px 20px !important;
            }
            body.store-admin-theme .eos-card:has(form) .eos-card-title {
                font-size: 20px;
            }
        }
    </style>

    <nav class="eos-bottom-nav">
        @php
            $bottomLinks = $staticMode
                ? [
                    ['route' => 'tenant.dashboard', 'active' => 'tenant.dashboard', 'label' => 'Home', 'icon' => 'ti-layout-dashboard'],
                    ['url' => url('/'), 'active' => 'never', 'label' => 'Website', 'icon' => 'ti-world'],
                    ['route' => 'tenant.infrastructure', 'active' => 'tenant.infrastructure*', 'label' => 'Billing', 'icon' => 'ti-receipt'],
                    ['route' => 'tenant.tickets', 'active' => 'tenant.tickets*', 'label' => 'Support', 'icon' => 'ti-lifebuoy'],
                ]
                : (($t?->site_type ?? null) === 'business'
                ? array_values(array_filter([
                    ['route' => 'tenant.dashboard', 'active' => 'tenant.dashboard', 'label' => 'Home', 'icon' => 'ti-layout-dashboard'],
                    $t->hasModule('enquiries') ? ['route' => 'tenant.business-inbox', 'active' => 'tenant.business-inbox*', 'label' => 'Enquiries', 'icon' => 'ti-inbox'] : null,
                    $t->hasModule('services') ? ['route' => 'tenant.services', 'active' => 'tenant.services*', 'label' => 'Services', 'icon' => 'ti-briefcase-2'] : null,
                    ['route' => 'tenant.theme', 'active' => 'tenant.theme*', 'label' => 'Website', 'icon' => 'ti-world'],
                ]))
                : (($t?->site_type ?? null) === 'school'
                ? array_values(array_filter([
                    ['route' => 'tenant.dashboard', 'active' => 'tenant.dashboard', 'label' => 'Home', 'icon' => 'ti-layout-dashboard'],
                    $t->hasModule('enquiry_form') ? ['route' => 'tenant.business-inbox', 'active' => 'tenant.business-inbox*', 'label' => 'Enquiries', 'icon' => 'ti-inbox'] : null,
                    $t->hasModule('academics') ? ['route' => 'tenant.business-content.index', 'params' => ['type' => 'academics'], 'active' => 'tenant.business-content.*', 'label' => 'Academics', 'icon' => 'ti-book'] : null,
                    ['route' => 'tenant.theme', 'active' => 'tenant.theme*', 'label' => 'Website', 'icon' => 'ti-world'],
                ]))
                : array_values(array_filter([
                    ['route' => 'tenant.dashboard', 'active' => 'tenant.dashboard', 'label' => 'Home', 'icon' => 'ti-layout-dashboard'],
                    ($t && $t->hasModule('orders') && Route::has('tenant.orders')) ? ['route' => 'tenant.orders', 'active' => 'tenant.orders*', 'label' => 'Orders', 'icon' => 'ti-truck-delivery'] : null,
                    ($t && $t->hasModule('catalog') && Route::has('tenant.catalog')) ? ['route' => 'tenant.catalog', 'active' => 'tenant.catalog*', 'label' => 'Products', 'icon' => 'ti-package'] : null,
                    ($t && $t->hasModule('customer_accounts') && Route::has('tenant.customers')) ? ['route' => 'tenant.customers', 'active' => 'tenant.customers*', 'label' => 'Customers', 'icon' => 'ti-user-circle'] : null,
                ]))));
            $bottomLinks = array_slice($bottomLinks, 0, 4);
        @endphp
        @foreach ($bottomLinks as $item)
            <a href="{{ isset($item['url']) ? $item['url'] : route($item['route']) }}"
               class="{{ request()->routeIs($item['active']) ? 'active' : '' }}">
                <i class="ti {{ $item['icon'] }}"></i> {{ $item['label'] }}
            </a>
        @endforeach
        <button type="button" @click="open = true"><i class="ti ti-menu-2"></i> Menu</button>
    </nav>
</div>
</body>
</html>
