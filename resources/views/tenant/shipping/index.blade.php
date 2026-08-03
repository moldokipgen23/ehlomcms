@extends('tenant.layouts.dashboard')

@section('title', 'Shipping Rules')
@section('subtitle', 'Delivery fees, pincode rules, and free shipping thresholds')

@section('content')
<div class="store-module-shell">
    <section class="store-module-hero"><div><div class="store-module-kicker">Fulfilment</div><div class="store-module-title">Shipping rules</div><div class="store-module-copy">Add delivery fee rules by pincode prefix and free-shipping thresholds.</div></div><div class="store-module-stats"><div class="store-mini-stat"><strong>{{ $rules->count() }}</strong><span>Rules</span></div></div></section>
    <section class="store-panel-clean" style="padding:20px;">
        <form method="POST" action="{{ route('tenant.shipping-rules.store') }}" style="display:grid;grid-template-columns:1fr 140px 120px 140px auto;gap:10px;align-items:end;">
            @csrf
            <div class="eos-field"><label class="eos-label">Name</label><input name="name" class="eos-input" placeholder="Local Delivery" required></div>
            <div class="eos-field"><label class="eos-label">Pincode starts</label><input name="pincode_pattern" class="eos-input" placeholder="560"></div>
            <div class="eos-field"><label class="eos-label">Fee</label><input type="number" step="0.01" name="fee" class="eos-input" required></div>
            <div class="eos-field"><label class="eos-label">Free above</label><input type="number" step="0.01" name="free_above" class="eos-input"></div>
            <button class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Add</button>
        </form>
    </section>
    <section class="store-panel-clean">
        @forelse ($rules as $rule)
            <div class="store-record-row"><div class="store-record-thumb"><i class="ti ti-truck-delivery"></i></div><div style="flex:1;"><div class="store-record-name">{{ $rule->name }}</div><div class="store-record-meta">{{ $rule->pincode_pattern ? 'Pincode starts ' . $rule->pincode_pattern : 'All pincodes' }} · Fee ₹{{ number_format($rule->fee, 2) }} @if($rule->free_above) · Free above ₹{{ number_format($rule->free_above, 2) }} @endif</div></div><span class="eos-badge badge-active">Active</span></div>
        @empty
            <div class="store-empty-state"><div><i class="ti ti-truck-delivery"></i><div class="store-empty-title">No shipping rules</div><div class="store-empty-copy">Add one rule to begin charging delivery fees.</div></div></div>
        @endforelse
    </section>
</div>
@endsection
