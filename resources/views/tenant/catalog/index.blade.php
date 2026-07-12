@extends('tenant.layouts.dashboard')

@section('title', 'Catalog')

@section('topbar-action')
    <a href="{{ route('tenant.catalog.create') }}" class="eos-btn eos-btn-primary" style="text-decoration:none;">
        <i class="ti ti-plus"></i> Add Product
    </a>
@endsection

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">Your Products</div>
            <span class="eos-card-link">{{ $products->count() }} items</span>
        </div>

        @forelse ($products as $product)
            <div class="eos-list-item">
                <div style="width:60px;height:60px;border-radius:8px;overflow:hidden;flex-shrink:0;background:var(--bg-hover);">
                    @if ($product->photo)
                        <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-dim);"><i class="ti ti-box"></i></div>
                    @endif
                </div>
                <div style="flex:1;min-width:0;padding:0 12px;">
                    <div class="eos-row-name">{{ $product->name }}</div>
                    <div class="eos-row-type">
                        @if ($product->category)
                            <span class="eos-badge badge-info" style="text-transform:capitalize;">{{ $product->category }}</span>
                        @endif
                        {{ $product->description ? Str::limit($product->description, 60) : '' }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="eos-amt">₹{{ number_format($product->price, 2) }}</div>
                    <div style="display:flex;gap:6px;margin-top:4px;justify-content:flex-end;">
                        <a href="{{ route('tenant.catalog.edit', $product->id) }}" class="eos-logout" title="Edit"><i class="ti ti-pencil"></i></a>
                        <form method="POST" action="{{ route('tenant.catalog.destroy', $product->id) }}" onsubmit="return confirm('Delete this product?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="eos-logout" title="Delete"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:32px 16px;">
                No products yet. <a href="{{ route('tenant.catalog.create') }}" style="color:var(--accent-blue);">Add your first product</a>.
            </div>
        @endforelse
    </div>
</div>
@endsection
