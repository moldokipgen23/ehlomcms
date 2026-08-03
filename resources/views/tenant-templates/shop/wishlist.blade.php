<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $tenant->name }} — Wishlist</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="antialiased">
<div style="max-width:1100px;margin:40px auto;padding:0 18px;">
    <a href="{{ route('tenant.home') }}" style="color:var(--text-muted);text-decoration:none;">← Back to store</a>
    <h1 style="margin:18px 0;color:var(--text-primary);">Wishlist</h1>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;">
        @forelse ($items as $item)
            @if ($item->product)
                <div class="eos-card" style="padding:14px;">
                    @if ($item->product->main_image)<img src="{{ Storage::url($item->product->main_image) }}" style="width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:10px;">@endif
                    <div class="eos-row-name">{{ $item->product->name }}</div>
                    <div class="eos-amt">₹{{ number_format($item->product->price, 2) }}</div>
                    <form method="POST" action="{{ route('tenant.cart.add', $item->product->id) }}" style="margin-top:10px;">@csrf<button class="eos-btn eos-btn-primary" style="width:100%;">Add to cart</button></form>
                </div>
            @endif
        @empty
            <div class="eos-empty">No wishlist items yet.</div>
        @endforelse
    </div>
</div>
</body>
</html>
