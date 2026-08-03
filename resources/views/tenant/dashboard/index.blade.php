@extends('tenant.layouts.dashboard')

@section('title', $tenant->site_type === 'business' ? 'Business Overview' : ($tenant->site_type === 'school' ? 'School Overview' : 'Store Overview'))
@section('subtitle', $tenant->site_type === 'business' ? 'Enquiries, services, projects, people, and website content' : ($tenant->site_type === 'school' ? 'Website content, admissions, academics, and school updates' : 'Sales, orders, products, and store analytics'))

@section('content')
    @if($tenant->site_type === 'business')
        @include('tenant.dashboard.business')
    @elseif($tenant->site_type === 'school')
        @include('tenant.dashboard.school')
    @else
    @php
        $money = fn ($amount) => '₹' . number_format((float) $amount, 0);
        $checkoutReady = $tenant->hasModule('checkout') || $tenant->hasModule('cart');
        $paymentLabel = match ($tenant->action_type) {
            'razorpay' => 'Razorpay',
            'whatsapp' => 'WhatsApp order',
            'custom' => $tenant->custom_gateway_name ?: 'Custom payment',
            default => $tenant->hasModule('payments') ? 'Payment methods active' : 'Not configured',
        };
        $setupItems = [
            ['label' => 'Products', 'done' => $productCount > 0, 'href' => Route::has('tenant.catalog') ? route('tenant.catalog') : '#'],
            ['label' => 'Checkout', 'done' => $checkoutReady, 'href' => Route::has('tenant.payments') ? route('tenant.payments') : '#'],
            ['label' => 'Payments', 'done' => $tenant->hasModule('payments') || filled($tenant->action_type), 'href' => Route::has('tenant.payments') ? route('tenant.payments') : '#'],
            ['label' => 'Policies', 'done' => $policyReadyCount >= 4, 'href' => Route::has('tenant.theme') ? route('tenant.theme') . '?tab=policies' : '#'],
            ['label' => 'Pages', 'done' => $customPageCount > 0, 'href' => Route::has('tenant.custom-pages') ? route('tenant.custom-pages') : '#'],
        ];
        $setupDone = collect($setupItems)->where('done', true)->count();
        $readiness = round(($setupDone / max(count($setupItems), 1)) * 100);
        $lastSaleAmount = $lastSale ? ($lastSale->total ?: $lastSale->amount) : 0;
    @endphp

    <style>
        .store-dashboard { display: grid; gap: 18px; }
        .store-panel, .store-card {
            border: 1px solid var(--border-card);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(15,23,42,.06);
        }
        .store-summary-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 16px;
            padding: 18px;
        }
        .store-eyebrow { color: #2563eb; font-size: 10px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; }
        .store-title { margin-top: 6px; color: var(--text-primary); font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 900; line-height: 1.15; }
        .store-copy { margin-top: 6px; color: var(--text-muted); font-size: 12.5px; line-height: 1.6; max-width: 760px; }
        .store-hero-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }
        .store-primary-btn, .store-secondary-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 38px; padding: 0 14px; border-radius: 9px; text-decoration: none;
            font-size: 12.5px; font-weight: 900;
        }
        .store-primary-btn { background: #2563eb; color: #fff; box-shadow: 0 10px 22px rgba(37,99,235,.18); }
        .store-secondary-btn { border: 1px solid #d9e2ef; color: #334155; background: #fff; }
        .store-card-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .store-card { padding: 16px; min-height: 126px; position: relative; overflow: hidden; }
        .store-card::before { content: ''; position: absolute; inset: 0 0 auto; height: 3px; background: var(--card-accent, #2563eb); }
        .store-card-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .store-card-label { color: var(--text-muted); font-size: 11px; font-weight: 900; letter-spacing: .6px; text-transform: uppercase; }
        .store-card-icon { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: #fff; background: var(--card-accent, #2563eb); }
        .store-card-value { margin-top: 16px; color: var(--text-primary); font-size: 25px; font-weight: 900; line-height: 1; }
        .store-card-meta { margin-top: 8px; color: var(--text-muted); font-size: 11.5px; line-height: 1.45; }
        .store-grid-main { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(330px, .75fr); gap: 14px; }
        .store-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .store-panel { padding: 16px; }
        .store-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .store-panel-title { color: var(--text-primary); font-size: 14px; font-weight: 900; }
        .store-panel-sub { color: var(--text-muted); font-size: 11px; margin-top: 2px; }
        .store-row {
            display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #eef2f7;
        }
        .store-row:last-child { border-bottom: 0; }
        .store-row-main { flex: 1; min-width: 0; }
        .store-row-title { color: var(--text-primary); font-size: 12.5px; font-weight: 900; }
        .store-row-meta { color: var(--text-muted); font-size: 11px; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .store-row-amount { color: var(--text-secondary); font-size: 12px; font-weight: 900; }
        .store-rank {
            width: 30px; height: 30px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
            background: #eef4ff; color: #2563eb; font-size: 12px; font-weight: 900; flex-shrink: 0;
        }
        .store-empty { padding: 24px 10px; text-align: center; color: var(--text-muted); font-size: 12px; line-height: 1.6; }
        .store-progress { height: 8px; border-radius: 999px; background: #e8eef7; overflow: hidden; }
        .store-progress span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #059669, #2563eb); }
        .store-check-mini { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; }
        .store-check-mini a {
            display: inline-flex; align-items: center; gap: 6px; padding: 7px 9px; border-radius: 999px;
            background: #f8fafc; border: 1px solid #e5edf7; color: #475569; text-decoration: none; font-size: 11px; font-weight: 900;
        }
        .store-check-mini a.ready { background: #ecfdf5; border-color: #bbf7d0; color: #047857; }
        .store-status-grid { display: grid; gap: 10px; }
        .store-status-line { display: flex; align-items: center; justify-content: space-between; gap: 12px; color: var(--text-muted); font-size: 12px; }
        .store-status-line strong { color: var(--text-primary); }
        @media (max-width: 1180px) {
            .store-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .store-grid-main, .store-grid-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 700px) {
            .store-summary-hero { grid-template-columns: 1fr; }
            .store-hero-actions { justify-content: flex-start; }
            .store-primary-btn, .store-secondary-btn { flex: 1 1 calc(50% - 5px); padding: 0 10px; }
            .store-card-grid { grid-template-columns: 1fr; }
            .store-title { font-size: 23px; }
        }
    </style>

    <div class="store-dashboard">
        <section class="store-panel store-summary-hero">
            <div>
                <div class="store-eyebrow">Commerce Dashboard</div>
                <div class="store-title">{{ $tenant->name }} sales overview</div>
                <div class="store-copy">Track earnings, orders, top products, stock health, and the latest store activity from one clean dashboard.</div>
            </div>
            <div class="store-hero-actions">
                @if (Route::has('tenant.catalog.create'))
                    <a href="{{ route('tenant.catalog.create') }}" class="store-primary-btn"><i class="ti ti-plus"></i> Add Product</a>
                @endif
                @if (Route::has('tenant.orders'))
                    <a href="{{ route('tenant.orders') }}" class="store-secondary-btn"><i class="ti ti-truck-delivery"></i> Orders</a>
                @endif
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="store-secondary-btn"><i class="ti ti-eye"></i> View Store</a>
            </div>
        </section>

        <section class="store-card-grid">
            <div class="store-card" style="--card-accent:#059669;">
                <div class="store-card-top"><div class="store-card-label">Total Earnings</div><div class="store-card-icon"><i class="ti ti-currency-rupee"></i></div></div>
                <div class="store-card-value">{{ $money($revenueTotal) }}</div>
                <div class="store-card-meta">{{ $orderCount }} orders recorded</div>
            </div>
            <div class="store-card" style="--card-accent:#2563eb;">
                <div class="store-card-top"><div class="store-card-label">This Month</div><div class="store-card-icon"><i class="ti ti-calendar-stats"></i></div></div>
                <div class="store-card-value">{{ $money($monthRevenue) }}</div>
                <div class="store-card-meta">Today: {{ $money($todayRevenue) }}</div>
            </div>
            <div class="store-card" style="--card-accent:#f59e0b;">
                <div class="store-card-top"><div class="store-card-label">Last Sale</div><div class="store-card-icon"><i class="ti ti-receipt"></i></div></div>
                <div class="store-card-value">{{ $lastSale ? $money($lastSaleAmount) : '—' }}</div>
                <div class="store-card-meta">{{ $lastSale ? (($lastSale->shipping_name ?: 'Customer') . ' · ' . $lastSale->created_at?->format('d M, h:i A')) : 'No completed sale yet' }}</div>
            </div>
            <div class="store-card" style="--card-accent:#7c3aed;">
                <div class="store-card-top"><div class="store-card-label">Avg Order</div><div class="store-card-icon"><i class="ti ti-chart-bar"></i></div></div>
                <div class="store-card-value">{{ $money($averageOrderValue) }}</div>
                <div class="store-card-meta">{{ $pendingOrderCount }} pending · {{ $paidOrderCount }} paid/delivered</div>
            </div>
        </section>

        <section class="store-grid-main">
            <div class="store-panel">
                <div class="store-panel-head">
                    <div>
                        <div class="store-panel-title">Recent Sales & Orders</div>
                        <div class="store-panel-sub">Newest checkout, COD, Razorpay, or WhatsApp orders</div>
                    </div>
                    @if (Route::has('tenant.orders'))
                        <a href="{{ route('tenant.orders') }}" class="eos-card-link">View all</a>
                    @endif
                </div>
                @forelse ($recentOrders as $order)
                    <div class="store-row">
                        <div class="store-rank"><i class="ti ti-shopping-bag"></i></div>
                        <div class="store-row-main">
                            <div class="store-row-title">{{ $order->order_id ?? ('Order #' . $order->id) }}</div>
                            <div class="store-row-meta">
                                {{ $order->shipping_name ?: 'Customer' }} · {{ ucfirst($order->status ?? 'new') }} · {{ $order->created_at?->format('d M, h:i A') }}
                            </div>
                        </div>
                        <div class="store-row-amount">{{ $money($order->total ?: $order->amount) }}</div>
                    </div>
                @empty
                    <div class="store-empty">No sales yet. Once customers order, the latest sales will appear here.</div>
                @endforelse
            </div>

            <div class="store-panel">
                <div class="store-panel-head">
                    <div>
                        <div class="store-panel-title">Top Selling Products</div>
                        <div class="store-panel-sub">Ranked by units sold</div>
                    </div>
                </div>
                @forelse ($topProducts as $index => $product)
                    <div class="store-row">
                        <div class="store-rank">#{{ $index + 1 }}</div>
                        <div class="store-row-main">
                            <div class="store-row-title">{{ $product->product_name }}</div>
                            <div class="store-row-meta">{{ (int) $product->units_sold }} units sold</div>
                        </div>
                        <div class="store-row-amount">{{ $money($product->sales_total) }}</div>
                    </div>
                @empty
                    <div class="store-empty">No product sales yet. Add products and start taking orders to see best sellers.</div>
                @endforelse
            </div>
        </section>

        <section class="store-grid-2">
            <div class="store-panel">
                <div class="store-panel-head">
                    <div>
                        <div class="store-panel-title">Catalog & Stock Summary</div>
                        <div class="store-panel-sub">Product availability at a glance</div>
                    </div>
                </div>
                <div class="store-status-grid">
                    <div class="store-status-line"><span>Active products</span><strong>{{ $activeProductCount }}/{{ $productCount }}</strong></div>
                    <div class="store-status-line"><span>Stock units tracked</span><strong>{{ number_format($inventoryUnits) }}</strong></div>
                    <div class="store-status-line"><span>Low-stock items</span><strong>{{ $lowStockCount }}</strong></div>
                    <div class="store-status-line"><span>Gallery images</span><strong>{{ $galleryCount }}</strong></div>
                </div>
            </div>

            <div class="store-panel">
                <div class="store-panel-head">
                    <div>
                        <div class="store-panel-title">Store Health</div>
                        <div class="store-panel-sub">{{ $setupDone }} of {{ count($setupItems) }} essentials complete</div>
                    </div>
                    <strong style="color:#2563eb;font-size:13px;">{{ $readiness }}%</strong>
                </div>
                <div class="store-progress"><span style="width: {{ $readiness }}%;"></span></div>
                <div class="store-status-grid" style="margin-top:14px;">
                    <div class="store-status-line"><span>Checkout</span><strong>{{ $checkoutReady ? 'Active' : 'Needs setup' }}</strong></div>
                    <div class="store-status-line"><span>Payment mode</span><strong>{{ $paymentLabel }}</strong></div>
                    <div class="store-status-line"><span>Policies</span><strong>{{ $policyReadyCount }}/4 filled</strong></div>
                    <div class="store-status-line"><span>Custom pages</span><strong>{{ $publishedCustomPageCount }}/{{ $customPageCount }} published</strong></div>
                </div>
                <div class="store-check-mini">
                    @foreach ($setupItems as $item)
                        <a href="{{ $item['href'] }}" class="{{ $item['done'] ? 'ready' : '' }}"><i class="ti {{ $item['done'] ? 'ti-check' : 'ti-minus' }}"></i> {{ $item['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
    @endif
@endsection
