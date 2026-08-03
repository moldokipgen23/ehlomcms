@extends('tenant.layouts.dashboard')

@section('title', $label)
@section('subtitle', 'Paid ecommerce feature settings')

@section('content')
@php
    $settings = $setting->settings ?? [];
    $money = fn ($amount) => '₹' . number_format((float) $amount, 2);
@endphp

<div class="store-module-shell">
    <section class="store-module-hero">
        <div>
            <div class="store-module-kicker">Paid Feature</div>
            <div class="store-module-title">{{ $label }}</div>
            <div class="store-module-copy">
                This module is active for the store. Use this page to manage the feature, track usage, and keep the tenant dashboard ready for a full ecommerce workflow.
            </div>
        </div>
        <div class="store-module-stats">
            <div class="store-mini-stat"><strong>{{ $setting->is_active ? 'On' : 'Off' }}</strong><span>Status</span></div>
        </div>
    </section>

    @if ($feature === 'advanced_analytics')
        <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;">
            <div class="eos-stat"><div class="eos-stat-label">Revenue</div><div class="eos-stat-value">{{ $money($data['revenue'] ?? 0) }}</div></div>
            <div class="eos-stat"><div class="eos-stat-label">Orders</div><div class="eos-stat-value">{{ $data['orders'] ?? 0 }}</div></div>
            <div class="eos-stat"><div class="eos-stat-label">Customers</div><div class="eos-stat-value">{{ $data['customers'] ?? 0 }}</div></div>
            <div class="eos-stat"><div class="eos-stat-label">Products</div><div class="eos-stat-value">{{ $data['products'] ?? 0 }}</div></div>
        </section>
        <section class="store-panel-clean">
            <div class="store-panel-clean-head"><div><div class="store-panel-clean-title">Recent Orders</div><div class="store-panel-clean-sub">Latest sales activity for the store.</div></div></div>
            @forelse (($data['recentOrders'] ?? collect()) as $order)
                <div class="store-record-row">
                    <div class="store-record-thumb"><i class="ti ti-receipt"></i></div>
                    <div><div class="store-record-name">{{ $order->order_id }}</div><div class="store-record-meta">{{ $order->shipping_name }} · {{ ucfirst($order->status) }}</div></div>
                    <div class="store-record-actions"><strong>{{ $money($order->total) }}</strong></div>
                </div>
            @empty
                <div class="store-empty-state"><div><i class="ti ti-chart-bar"></i><p>No analytics data yet.</p></div></div>
            @endforelse
        </section>
    @elseif ($feature === 'abandoned_cart')
        <section class="store-panel-clean">
            <div class="store-panel-clean-head"><div><div class="store-panel-clean-title">Abandoned Carts</div><div class="store-panel-clean-sub">Captured from storefront cart and checkout sessions.</div></div></div>
            @forelse (($data['carts'] ?? collect()) as $cart)
                <div class="store-record-row">
                    <div class="store-record-thumb"><i class="ti ti-shopping-cart-exclamation"></i></div>
                    <div><div class="store-record-name">{{ $cart->customer_phone ?: ($cart->customer_email ?: 'Unknown shopper') }}</div><div class="store-record-meta">{{ $cart->created_at->diffForHumans() }} · {{ count($cart->cart_data ?? []) }} item lines</div></div>
                    <div class="store-record-actions"><strong>{{ $money($cart->subtotal) }}</strong><span class="eos-badge {{ $cart->recovered_at ? 'eos-badge-success' : '' }}">{{ $cart->recovered_at ? 'Recovered' : 'Open' }}</span></div>
                </div>
            @empty
                <div class="store-empty-state"><div><i class="ti ti-shopping-cart"></i><p>No abandoned carts captured yet.</p></div></div>
            @endforelse
        </section>
    @elseif ($feature === 'loyalty_rewards')
        <form method="POST" action="{{ route('tenant.commerce-feature.update', $feature) }}" class="store-panel-clean" style="padding:20px;">
            @csrf
            <label style="display:flex;gap:10px;align-items:center;font-size:13px;font-weight:800;color:var(--text-secondary);"><input type="checkbox" name="is_active" value="1" @checked($setting->is_active)> Enable loyalty rewards</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-top:16px;">
                <div class="eos-field"><label class="eos-label">Points per ₹100</label><input name="points_per_100" type="number" min="1" class="eos-input" value="{{ $settings['points_per_100'] ?? 1 }}"></div>
                <div class="eos-field"><label class="eos-label">Redeem Value per Point</label><input name="redeem_value" type="number" step="0.01" min="0" class="eos-input" value="{{ $settings['redeem_value'] ?? 1 }}"></div>
            </div>
            <button class="eos-btn eos-btn-primary"><i class="ti ti-device-floppy"></i> Save Loyalty Settings</button>
        </form>
        <section class="store-panel-clean">
            <div class="store-panel-clean-head"><div><div class="store-panel-clean-title">Loyalty Ledger</div><div class="store-panel-clean-sub">Points earned by customer orders.</div></div></div>
            @forelse (($data['transactions'] ?? collect()) as $txn)
                <div class="store-record-row"><div class="store-record-thumb"><i class="ti ti-gift"></i></div><div><div class="store-record-name">{{ $txn->customer?->name ?? 'Customer' }}</div><div class="store-record-meta">{{ $txn->notes }} · {{ $txn->created_at->diffForHumans() }}</div></div><div class="store-record-actions"><strong>{{ $txn->points }} pts</strong></div></div>
            @empty
                <div class="store-empty-state"><div><i class="ti ti-gift"></i><p>No loyalty points awarded yet.</p></div></div>
            @endforelse
        </section>
    @elseif ($feature === 'subscription_products')
        <form method="POST" action="{{ route('tenant.commerce-feature.update', $feature) }}" class="store-panel-clean" style="padding:20px;">
            @csrf
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;align-items:end;">
                <div class="eos-field"><label class="eos-label">Plan Name</label><input name="name" class="eos-input" required></div>
                <div class="eos-field"><label class="eos-label">Product</label><select name="tenant_product_id" class="eos-input"><option value="">General plan</option>@foreach (($data['products'] ?? collect()) as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select></div>
                <div class="eos-field"><label class="eos-label">Interval</label><select name="interval" class="eos-input"><option value="weekly">Weekly</option><option value="monthly" selected>Monthly</option><option value="quarterly">Quarterly</option><option value="yearly">Yearly</option></select></div>
                <div class="eos-field"><label class="eos-label">Price</label><input name="price" type="number" step="0.01" min="0" class="eos-input" required></div>
                <button class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Add</button>
            </div>
        </form>
        <section class="store-panel-clean">
            @forelse (($data['plans'] ?? collect()) as $plan)
                <div class="store-record-row"><div class="store-record-thumb"><i class="ti ti-refresh"></i></div><div><div class="store-record-name">{{ $plan->name }}</div><div class="store-record-meta">{{ $plan->product?->name ?? 'General subscription' }} · {{ ucfirst($plan->interval) }}</div></div><div class="store-record-actions"><strong>{{ $money($plan->price) }}</strong></div></div>
            @empty
                <div class="store-empty-state"><div><i class="ti ti-refresh"></i><p>No subscription plans yet.</p></div></div>
            @endforelse
        </section>
    @elseif ($feature === 'bulk_import_export')
        <section class="store-panel-clean" style="padding:20px;">
            <form method="POST" action="{{ route('tenant.commerce-feature.import-products') }}" enctype="multipart/form-data">
                @csrf
                <div class="eos-field"><label class="eos-label">Import Products CSV</label><input type="file" name="csv" accept=".csv,text/csv" class="eos-input" required></div>
                <button class="eos-btn eos-btn-primary"><i class="ti ti-upload"></i> Import CSV</button>
                <a class="eos-btn eos-btn-secondary" href="{{ route('tenant.commerce-feature.export-products', $feature) }}"><i class="ti ti-download"></i> Export Products</a>
            </form>
        </section>
    @elseif ($feature === 'multi_vendor')
        <form method="POST" action="{{ route('tenant.commerce-feature.update', $feature) }}" class="store-panel-clean" style="padding:20px;">
            @csrf
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;align-items:end;">
                <div class="eos-field"><label class="eos-label">Vendor Name</label><input name="name" class="eos-input" required></div>
                <div class="eos-field"><label class="eos-label">Email</label><input name="email" type="email" class="eos-input"></div>
                <div class="eos-field"><label class="eos-label">Phone</label><input name="phone" class="eos-input"></div>
                <div class="eos-field"><label class="eos-label">Commission %</label><input name="commission_rate" type="number" step="0.01" min="0" max="100" class="eos-input" value="0"></div>
                <button class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Add</button>
            </div>
        </form>
        <section class="store-panel-clean">
            @forelse (($data['vendors'] ?? collect()) as $vendor)
                <div class="store-record-row"><div class="store-record-thumb"><i class="ti ti-building-store"></i></div><div><div class="store-record-name">{{ $vendor->name }}</div><div class="store-record-meta">{{ $vendor->email ?: 'No email' }} · {{ $vendor->phone ?: 'No phone' }}</div></div><div class="store-record-actions"><strong>{{ $vendor->commission_rate }}%</strong></div></div>
            @empty
                <div class="store-empty-state"><div><i class="ti ti-building-store"></i><p>No vendors yet.</p></div></div>
            @endforelse
        </section>
    @elseif ($feature === 'pos_integration')
        <form method="POST" action="{{ route('tenant.commerce-feature.update', $feature) }}" class="store-panel-clean" style="padding:20px;">
            @csrf
            <label style="display:flex;gap:10px;align-items:center;font-size:13px;font-weight:800;color:var(--text-secondary);"><input type="checkbox" name="is_active" value="1" @checked($setting->is_active)> Enable POS export mode</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-top:16px;">
                <div class="eos-field"><label class="eos-label">POS Provider</label><input name="provider" class="eos-input" value="{{ $settings['provider'] ?? '' }}" placeholder="Square, Zoho, custom..."></div>
                <div class="eos-field"><label class="eos-label">Store Code</label><input name="store_code" class="eos-input" value="{{ $settings['store_code'] ?? '' }}"></div>
            </div>
            <button class="eos-btn eos-btn-primary"><i class="ti ti-device-floppy"></i> Save POS Settings</button>
            <a class="eos-btn eos-btn-secondary" href="{{ route('tenant.commerce-feature.export-products', $feature) }}"><i class="ti ti-download"></i> Export Product Feed</a>
        </form>
    @else
        <form method="POST" action="{{ route('tenant.commerce-feature.update', $feature) }}" class="store-panel-clean" style="padding:20px;">
            @csrf
            <label style="display:flex;gap:10px;align-items:center;font-size:13px;font-weight:800;color:var(--text-secondary);"><input type="checkbox" name="is_active" value="1" @checked($setting->is_active)> Enable {{ $label }}</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:16px;">
                <div class="eos-field"><label class="eos-label">Internal Notes</label><textarea name="notes" class="eos-input" rows="4">{{ $settings['notes'] ?? '' }}</textarea></div>
                <div class="eos-field"><label class="eos-label">Customer-facing Label</label><input name="label" class="eos-input" value="{{ $settings['label'] ?? $label }}"></div>
            </div>
            <button class="eos-btn eos-btn-primary"><i class="ti ti-device-floppy"></i> Save Settings</button>
        </form>
    @endif
</div>
@endsection
