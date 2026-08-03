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
                        {{ $order->shipping_name ?: ($order->customer_details['name'] ?? 'Customer') }}
                        @if ($order->shipping_phone)
                            &middot; {{ $order->shipping_phone }}
                        @endif
                        @if ($order->customer_email)
                            &middot; {{ $order->customer_email }}
                        @endif
                    </div>
                    <div class="eos-row-type" style="margin-top:3px;">
                        @if ($order->product)
                            {{ $order->product->name }}
                        @elseif ($order->items->count())
                            {{ $order->items->map(fn($item) => trim(($item->product_name ?: $item->product?->name) . ' ' . ($item->color_name ? '(' . $item->color_name . ($item->size_label ? ' / ' . $item->size_label : '') . ')' : '')))->implode(', ') }}
                        @else
                            &mdash;
                        @endif
                        &middot; {{ $order->created_at->format('M j, Y g:i A') }}
                        @if ($order->payment_method)
                            &middot; {{ $order->payment_method }}
                        @endif
                        @if ($order->payment_status)
                            &middot; {{ $order->payment_status }}
                        @endif
                        @if ($order->notes)
                            &middot; {{ Str::limit($order->notes, 50) }}
                        @endif
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="eos-amt">₹{{ number_format($order->total ?? $order->amount, 0) }}</div>
                    @if ($tenant->hasModule('gst_invoice'))
                        <a href="{{ route('tenant.orders.invoice', $order->id) }}" target="_blank" class="eos-card-link">Invoice</a>
                    @endif
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
