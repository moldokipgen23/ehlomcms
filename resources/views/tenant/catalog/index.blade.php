@extends('tenant.layouts.dashboard')

@section('title', 'Catalog')

@section('topbar-action')
    <a href="{{ route('tenant.catalog.create') }}" class="eos-btn eos-btn-primary" style="text-decoration:none;">
        <i class="ti ti-plus"></i> Add Product
    </a>
@endsection

@section('content')
<div class="store-module-shell">
    <section class="store-module-hero">
        <div>
            <div class="store-module-kicker">Product Operations</div>
            <div class="store-module-title">Product catalog</div>
            <div class="store-module-copy">Manage live products, prices, media, collections, variants, and stock from the main catalog workspace.</div>
        </div>
        <div class="store-module-stats">
            <div class="store-mini-stat"><strong>{{ $products->count() }}</strong><span>Total products</span></div>
            <div class="store-mini-stat"><strong>{{ $products->where('is_active', true)->count() }}</strong><span>Visible live</span></div>
            <div class="store-mini-stat"><strong>{{ $products->sum('stock') }}</strong><span>Base stock</span></div>
        </div>
    </section>

    <section class="store-panel-clean">
        <div class="store-panel-clean-head">
            <div>
                <div class="store-panel-clean-title">Live product workspace</div>
                <div class="store-panel-clean-sub">Every item customers can browse, save, or order.</div>
            </div>
            <span class="eos-card-link">{{ $products->count() }} items</span>
        </div>

        @forelse ($products as $product)
            <div class="store-record-row">
                <div class="store-record-thumb">
                    @if ($product->main_image)
                        <img src="{{ Storage::url($product->main_image) }}" alt="{{ $product->name }}">
                    @else
                        <i class="ti ti-box"></i>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="store-record-name">
                        {{ $product->name }}
                        @unless ($product->is_active)
                            <span class="eos-badge badge-warning" style="margin-left:6px;">Hidden</span>
                        @endunless
                    </div>
                    <div class="store-record-meta">
                        @if ($product->productCategory)
                            <span class="eos-badge badge-info" style="text-transform:capitalize;">{{ $product->productCategory->name }}</span>
                        @endif
                        @if ($product->collections->count())
                            <span class="eos-badge badge-info">{{ $product->collections->pluck('name')->implode(', ') }}</span>
                        @endif
                        @if ($product->colors->count())
                            <span class="eos-badge badge-success">{{ $product->colors->count() }} colors</span>
                        @endif
                        @if ($product->sizes->count())
                            <span class="eos-badge badge-success">{{ $product->sizes->count() }} sizes</span>
                        @endif
                        @if ($product->variants->count())
                            <span class="eos-badge badge-warning">{{ $product->variants->count() }} variants</span>
                        @endif
                        {{ $product->description ? Str::limit($product->description, 70) : '' }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="eos-amt">₹{{ number_format($product->price, 2) }}</div>
                    <div class="eos-row-type">Stock: {{ $product->stock }} @if($product->sku) &middot; SKU: {{ $product->sku }} @endif</div>
                    <div class="store-record-actions" style="margin-top:4px;justify-content:flex-end;">
                        <a href="{{ route('tenant.catalog.edit', $product->id) }}" class="eos-logout" title="Edit"><i class="ti ti-pencil"></i></a>
                        <form method="POST" action="{{ route('tenant.catalog.destroy', $product->id) }}" onsubmit="return confirm('Delete this product?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="eos-logout" title="Delete"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="store-empty-state">
                <div>
                    <i class="ti ti-package"></i>
                    <div class="store-empty-title">No products yet</div>
                    <div class="store-empty-copy">Add the first product with images, pricing, stock, and checkout options to start selling.</div>
                    <a href="{{ route('tenant.catalog.create') }}" class="eos-btn eos-btn-primary" style="display:inline-flex;margin-top:14px;text-decoration:none;"><i class="ti ti-plus"></i> Add Product</a>
                </div>
            </div>
        @endforelse
    </section>
</div>
@endsection
