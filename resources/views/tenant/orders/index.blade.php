@extends('tenant.layouts.dashboard')

@section('title', 'Orders')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">Orders</div>
            <span class="eos-card-link">{{ $orders->count() }} total</span>
        </div>

        @forelse ($orders as $order)
            <div class="eos-list-item">
                <div class="eos-init" style="background:var(--bg-hover);">
                    <i class="ti {{ $order->status === 'paid' ? 'ti-circle-check' : 'ti-clock' }}" style="color:{{ $order->status === 'paid' ? 'var(--accent-green)' : 'var(--accent-amber)' }};"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name">{{ $order->order_id }}</div>
                    <div class="eos-row-type">
                        {{ $order->product?->name ?? '—' }}
                        &middot; {{ $order->created_at->format('M j, Y g:i A') }}
                        @if ($order->payment_method)
                            &middot; {{ $order->payment_method }}
                        @endif
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="eos-amt">₹{{ number_format($order->amount, 0) }}</div>
                    <span class="eos-badge badge-{{ $order->status === 'paid' ? 'active' : 'draft' }}">{{ strtoupper($order->status) }}</span>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:32px 16px;">No orders yet. Orders appear here when customers complete a purchase.</div>
        @endforelse
    </div>
</div>
@endsection
