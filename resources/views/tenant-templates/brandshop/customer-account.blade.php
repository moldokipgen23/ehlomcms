<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $tenant->name }} — My Account</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="antialiased">
<div style="max-width:960px;margin:40px auto;padding:0 18px;">
    <div style="display:flex;justify-content:space-between;align-items:center;"><a href="{{ route('tenant.home') }}" style="color:var(--text-muted);text-decoration:none;">← Back to store</a><form method="POST" action="{{ route('tenant.customer.logout') }}">@csrf<button class="eos-btn eos-btn-secondary">Logout</button></form></div>
    <h1 style="margin:18px 0;color:var(--text-primary);">Hi, {{ $customer->name }}</h1>
    <div class="eos-card" style="padding:20px;">
        <h2 style="font-size:18px;margin-bottom:14px;">Order History</h2>
        @forelse ($orders as $order)
            <div class="eos-list-item"><div style="flex:1;"><div class="eos-row-name">{{ $order->order_id }}</div><div class="eos-row-type">{{ ucfirst($order->status) }} · {{ $order->created_at->format('M j, Y') }}</div></div><div class="eos-amt">₹{{ number_format($order->total ?? $order->amount, 2) }}</div></div>
        @empty
            <div class="eos-empty">No orders yet.</div>
        @endforelse
    </div>
</div>
</body>
</html>
