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
                    <i class="ti {{ $order->status === 'paid' || $order->status === 'delivered' ? 'ti-circle-check' : ($order->status === 'cancelled' ? 'ti-circle-x' : 'ti-clock') }}" style="color:{{ $order->status === 'paid' || $order->status === 'delivered' ? 'var(--accent-green)' : ($order->status === 'cancelled' ? 'var(--accent-red)' : 'var(--accent-amber)') }};"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name">{{ $order->order_id }}</div>
                    <div class="eos-row-type">
                        @if ($order->product)
                            {{ $order->product->name }}
                        @elseif ($order->items->count())
                            {{ $order->items->pluck('product.name')->implode(', ') }}
                        @else
                            &mdash;
                        @endif
                        &middot; {{ $order->created_at->format('M j, Y g:i A') }}
                        @if ($order->payment_method)
                            &middot; {{ $order->payment_method }}
                        @endif
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="eos-amt">₹{{ number_format($order->amount, 0) }}</div>
                    <form action="{{ route('tenant.orders.update-status', $order) }}" method="POST" style="display:inline;">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="eos-select" style="font-size:11px;padding:3px 6px;border-radius:5px;border:1px solid var(--border);background:var(--bg-card);color:var(--text-primary);cursor:pointer;">
                            @foreach ($statuses as $s)
                                <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:32px 16px;">No orders yet. Orders appear here when customers complete a purchase.</div>
        @endforelse
    </div>
</div>
@endsection
