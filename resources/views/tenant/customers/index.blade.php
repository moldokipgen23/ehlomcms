@extends('tenant.layouts.dashboard')

@section('title', 'Customers')
@section('subtitle', 'Customer accounts, profiles, and order history')

@section('content')
<div class="store-module-shell">
    <section class="store-module-hero">
        <div><div class="store-module-kicker">Customer CRM</div><div class="store-module-title">Customer accounts</div><div class="store-module-copy">Customers who register on the storefront appear here with their contact details and order count.</div></div>
        <div class="store-module-stats"><div class="store-mini-stat"><strong>{{ $customers->count() }}</strong><span>Customers</span></div></div>
    </section>
    <section class="store-panel-clean">
        @forelse ($customers as $customer)
            <div class="store-record-row"><div class="store-record-thumb"><i class="ti ti-user-circle"></i></div><div style="flex:1;"><div class="store-record-name">{{ $customer->name }}</div><div class="store-record-meta">{{ $customer->email }} @if($customer->phone) · {{ $customer->phone }} @endif · {{ $customer->orders_count }} orders</div></div></div>
        @empty
            <div class="store-empty-state"><div><i class="ti ti-user-circle"></i><div class="store-empty-title">No customers yet</div><div class="store-empty-copy">Registered storefront customer accounts will show here.</div></div></div>
        @endforelse
    </section>
</div>
@endsection
