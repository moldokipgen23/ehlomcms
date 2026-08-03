@extends('tenant.layouts.dashboard')

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
<div class="store-module-shell">
    <section class="store-module-hero">
        <div>
            <div class="store-module-kicker">Paid Ecommerce Feature</div>
            <div class="store-module-title">{{ $title }}</div>
            <div class="store-module-copy">{{ $subtitle }}</div>
        </div>
        <div class="store-module-stats"><div class="store-mini-stat"><strong>{{ $items->count() }}</strong><span>Records</span></div></div>
    </section>

    @if ($feature === 'coupons')
        <section class="store-panel-clean" style="padding:20px;">
            <div class="store-panel-clean-title" style="font-size:16px;margin-bottom:14px;">Create coupon</div>
            <form method="POST" action="{{ route('tenant.coupons.store') }}" style="display:grid;grid-template-columns:1fr 130px 140px 140px 120px auto;gap:10px;align-items:end;">
                @csrf
                <div class="eos-field"><label class="eos-label">Code</label><input name="code" class="eos-input" placeholder="WELCOME10" required></div>
                <div class="eos-field"><label class="eos-label">Type</label><select name="type" class="eos-input"><option value="fixed">Fixed</option><option value="percent">Percent</option></select></div>
                <div class="eos-field"><label class="eos-label">Value</label><input type="number" step="0.01" name="value" class="eos-input" required></div>
                <div class="eos-field"><label class="eos-label">Min order</label><input type="number" step="0.01" name="min_order_amount" class="eos-input"></div>
                <label style="font-size:12px;color:var(--text-secondary);font-weight:700;"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Save</button>
            </form>
        </section>
    @endif

    <section class="store-panel-clean">
        <div class="store-panel-clean-head"><div><div class="store-panel-clean-title">{{ $title }} records</div><div class="store-panel-clean-sub">Tenant-owned feature data.</div></div></div>
        @forelse ($items as $item)
            <div class="store-record-row">
                <div class="store-record-thumb"><i class="ti ti-ticket"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="store-record-name">{{ $item->code }}</div>
                    <div class="store-record-meta">{{ ucfirst($item->type) }} · {{ $item->value }} · Used {{ $item->used_count }}{{ $item->usage_limit ? '/' . $item->usage_limit : '' }}</div>
                </div>
                <span class="eos-badge {{ $item->is_active ? 'badge-active' : 'badge-pending' }}">{{ $item->is_active ? 'Active' : 'Off' }}</span>
            </div>
        @empty
            <div class="store-empty-state"><div><i class="ti ti-ticket"></i><div class="store-empty-title">No records yet</div><div class="store-empty-copy">Create the first {{ strtolower($title) }} record.</div></div></div>
        @endforelse
    </section>
</div>
@endsection
