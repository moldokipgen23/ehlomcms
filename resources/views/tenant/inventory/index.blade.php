@extends('tenant.layouts.dashboard')

@section('title', 'Inventory')

@section('topbar-action')
    @if ($tenant->hasModule('variants'))
        <a href="{{ route('tenant.attributes') }}" class="eos-btn eos-btn-secondary" style="text-decoration:none;">
            <i class="ti ti-list-details"></i> Attributes
        </a>
    @endif
@endsection

@section('content')
<form method="POST" action="{{ route('tenant.inventory.update') }}">
    @csrf
    <div class="store-module-shell">
        <section class="store-module-hero">
            <div>
                <div class="store-module-kicker">Stock Control</div>
                <div class="store-module-title">Inventory desk</div>
                <div class="store-module-copy">Update SKUs, product stock, variant stock, and variant-specific pricing without opening each product one by one.</div>
            </div>
            <div class="store-module-stats">
                <div class="store-mini-stat"><strong>{{ $products->count() }}</strong><span>Products</span></div>
                <div class="store-mini-stat"><strong>{{ $products->sum('stock') }}</strong><span>Base stock</span></div>
                <div class="store-mini-stat"><strong>{{ $products->sum(fn ($product) => $product->variants->count()) }}</strong><span>Variants</span></div>
            </div>
        </section>

        <section class="store-panel-clean">
            <div class="store-panel-clean-head">
                <div>
                    <div class="store-panel-clean-title">Stock, SKU & variant pricing</div>
                    <div class="store-panel-clean-sub">Save all inventory edits in one update.</div>
                </div>
                <button class="eos-btn eos-btn-primary" style="border:none;"><i class="ti ti-device-floppy"></i> Save Inventory</button>
            </div>
            @forelse ($products as $product)
                <div style="padding:14px 16px;border-bottom:1px solid var(--border);">
                    <div style="display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap;">
                        <div>
                            <div class="eos-row-name">{{ $product->name }}</div>
                            <div class="eos-row-type">Base product inventory</div>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input class="eos-input" style="width:120px;" name="products[{{ $product->id }}][sku]" value="{{ $product->sku }}" placeholder="SKU">
                            <input class="eos-input" style="width:90px;" type="number" min="0" name="products[{{ $product->id }}][stock]" value="{{ $product->stock }}" placeholder="Stock">
                        </div>
                    </div>
                    @if ($product->variants->count())
                        <div style="margin-top:10px;border:1px solid var(--border);border-radius:8px;overflow:hidden;">
                            @foreach ($product->variants as $variant)
                                <div style="display:grid;grid-template-columns:1.2fr 1fr 90px 100px auto;gap:8px;align-items:center;padding:8px;border-bottom:1px solid var(--border);">
                                    <div style="font-size:12px;color:var(--text-primary);">{{ $variant->color?->color_name ?? 'Any color' }} / {{ $variant->size?->size_label ?? 'Any size' }}</div>
                                    <input class="eos-input" name="variants[{{ $variant->id }}][sku]" value="{{ $variant->sku }}" placeholder="SKU">
                                    <input class="eos-input" type="number" min="0" name="variants[{{ $variant->id }}][stock]" value="{{ $variant->stock }}" placeholder="Stock">
                                    <input class="eos-input" type="number" step="0.01" min="0" name="variants[{{ $variant->id }}][price]" value="{{ $variant->price }}" placeholder="Price">
                                    <label style="display:flex;gap:4px;align-items:center;font-size:11px;color:var(--text-secondary);"><input type="checkbox" name="variants[{{ $variant->id }}][is_active]" value="1" {{ $variant->is_active ? 'checked' : '' }}> Active</label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="store-empty-state">
                    <div>
                        <i class="ti ti-building-warehouse"></i>
                        <div class="store-empty-title">No inventory yet</div>
                        <div class="store-empty-copy">Add products first, then this screen becomes the fast stock control desk.</div>
                    </div>
                </div>
            @endforelse
        </section>
    </div>
</form>
@endsection
