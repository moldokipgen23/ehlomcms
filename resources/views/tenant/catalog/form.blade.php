@extends('tenant.layouts.dashboard')

@section('title', isset($product) ? 'Edit Product' : 'Add Product')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:920px;">
        <div class="eos-card-header">
            <div class="eos-card-title">{{ isset($product) ? 'Edit Product' : 'Add Product' }}</div>
        </div>

        <form method="POST" action="{{ isset($product) ? route('tenant.catalog.update', $product->id) : route('tenant.catalog.store') }}" enctype="multipart/form-data" style="padding:16px;">
            @csrf
            @if (isset($product)) @method('PUT') @endif

            <div class="eos-field">
                <label class="eos-label">Product Name *</label>
                <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" class="eos-input" required>
            </div>

            <div class="eos-field">
                <label class="eos-label">Price (₹) *</label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price ?? '') }}" class="eos-input" required>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
                <div class="eos-field">
                    <label class="eos-label">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" class="eos-input" placeholder="Optional SKU">
                </div>
                <div class="eos-field">
                    <label class="eos-label">Base Stock</label>
                    <input type="number" min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" class="eos-input">
                </div>
                <div class="eos-field">
                    <label class="eos-label">Sort Order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="eos-input">
                </div>
            </div>

            <div class="eos-field">
                <label class="eos-label">Category</label>
                <input type="text" name="category" list="category-list" value="{{ old('category', $product->productCategory->name ?? $product->category ?? '') }}" class="eos-input" placeholder="e.g. Women's, Men's, Accessories">
                <datalist id="category-list">
                    @foreach ($categories ?? [] as $category)
                        <option value="{{ $category->name }}"></option>
                    @endforeach
                </datalist>
                <div class="eos-row-type" style="margin-top:4px;">Imported new feature: categories are now reusable records.</div>
            </div>

            @if (($vendors ?? collect())->count())
                <div class="eos-field">
                    <label class="eos-label">Vendor / Seller</label>
                    <select name="tenant_vendor_id" class="eos-input">
                        <option value="">Store owned product</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(old('tenant_vendor_id', $product->tenant_vendor_id ?? '') == $vendor->id)>{{ $vendor->name }} — {{ $vendor->commission_rate }}% commission</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="eos-field">
                <label class="eos-label">Collections</label>
                <input type="text" name="collections" list="collection-list" value="{{ old('collections', isset($product) ? $product->collections->pluck('name')->implode(', ') : '') }}" class="eos-input" placeholder="Comma separated, e.g. Signature Series, New Arrivals">
                <datalist id="collection-list">
                    @foreach ($collections ?? [] as $collection)
                        <option value="{{ $collection->name }}"></option>
                    @endforeach
                </datalist>
                <div class="eos-row-type" style="margin-top:4px;">Imported new feature: collection membership for future collection pages and merchandising sections.</div>
            </div>

            <div class="eos-field">
                <label class="eos-label">Description</label>
                <textarea name="description" rows="4" class="eos-input" style="resize:vertical;">{{ old('description', $product->description ?? '') }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
                <div class="eos-field">
                    <label class="eos-label">Material</label>
                    <input type="text" name="material" value="{{ old('material', $product->material ?? '') }}" class="eos-input" placeholder="Cotton, silk blend...">
                </div>
                <div class="eos-field">
                    <label class="eos-label">Weight</label>
                    <input type="text" name="weight" value="{{ old('weight', $product->weight ?? '') }}" class="eos-input" placeholder="Optional">
                </div>
            </div>

            <div class="eos-field">
                <label class="eos-label">Care Instructions</label>
                <textarea name="care_instructions" rows="2" class="eos-input" style="resize:vertical;">{{ old('care_instructions', $product->care_instructions ?? '') }}</textarea>
            </div>

            <div class="eos-field">
                <label class="eos-label">Heritage / Product Story Note</label>
                <textarea name="heritage_note" rows="2" class="eos-input" style="resize:vertical;">{{ old('heritage_note', $product->heritage_note ?? '') }}</textarea>
            </div>

            <div class="eos-field">
                <label class="eos-label">Cover Photo</label>
                @if (isset($product) && $product->main_image)
                    <div style="margin-bottom:8px;">
                        <img src="{{ Storage::url($product->main_image) }}" alt="{{ $product->name }}" style="max-height:100px;border-radius:8px;border:1px solid var(--border);">
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*" class="eos-input">
                <div class="eos-row-type" style="margin-top:4px;">JPEG, PNG, WebP. Max 5MB.</div>
            </div>

            <div class="eos-field">
                <label class="eos-label">Gallery Images</label>
                <input type="file" name="images[]" accept="image/*" class="eos-input" multiple>
                @if (isset($product) && $product->images->count())
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                        @foreach ($product->images as $image)
                            <img src="{{ Storage::url($image->image_path) }}" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="eos-field">
                <label class="eos-label">Product Videos</label>
                <input type="file" name="videos[]" accept="video/mp4,video/quicktime,video/webm" class="eos-input" multiple>
                @if (isset($product) && $product->videos->count())
                    <div class="eos-row-type" style="margin-top:4px;">{{ $product->videos->count() }} uploaded video(s).</div>
                @endif
            </div>

            <div class="eos-field">
                <label class="eos-label">Colors</label>
                <textarea name="colors" rows="3" class="eos-input" placeholder="One per line: Indigo | #1B3A5C">{{ old('colors', isset($product) ? $product->colors->map(fn($c) => $c->color_name . ' | ' . $c->hex_code)->implode("\n") : '') }}</textarea>
                <div class="eos-row-type" style="margin-top:4px;">Imported new feature: color swatches. Format: name | hex code.</div>
            </div>

            <div class="eos-field">
                <label class="eos-label">Sizes</label>
                <input type="text" name="sizes" value="{{ old('sizes', isset($product) ? $product->sizes->pluck('size_label')->implode(', ') : '') }}" class="eos-input" placeholder="Comma separated, e.g. S, M, L, XL">
            </div>

            <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
                <label style="display:flex;gap:6px;align-items:center;font-size:12px;color:var(--text-secondary);">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}> Active
                </label>
                <label style="display:flex;gap:6px;align-items:center;font-size:12px;color:var(--text-secondary);">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}> Featured
                </label>
                <label style="display:flex;gap:6px;align-items:center;font-size:12px;color:var(--text-secondary);">
                    <input type="checkbox" name="is_top_seller" value="1" {{ old('is_top_seller', $product->is_top_seller ?? false) ? 'checked' : '' }}> Top Seller
                </label>
            </div>

            @if (isset($product) && $product->variants->count())
                <div class="eos-field">
                    <label class="eos-label">Variants</label>
                    <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;">
                        @foreach ($product->variants as $variant)
                            <div style="display:grid;grid-template-columns:1.3fr 1fr 1fr 1fr auto;gap:8px;align-items:center;padding:10px;border-bottom:1px solid var(--border);">
                                <div style="font-size:12px;color:var(--text-primary);">
                                    {{ $variant->color?->color_name ?? 'Any color' }} / {{ $variant->size?->size_label ?? 'Any size' }}
                                </div>
                                <input type="text" name="variants[{{ $variant->id }}][sku]" value="{{ old('variants.' . $variant->id . '.sku', $variant->sku) }}" class="eos-input" placeholder="SKU">
                                <input type="number" min="0" name="variants[{{ $variant->id }}][stock]" value="{{ old('variants.' . $variant->id . '.stock', $variant->stock) }}" class="eos-input" placeholder="Stock">
                                <input type="number" step="0.01" min="0" name="variants[{{ $variant->id }}][price]" value="{{ old('variants.' . $variant->id . '.price', $variant->price) }}" class="eos-input" placeholder="Price override">
                                <label style="display:flex;gap:4px;align-items:center;font-size:11px;color:var(--text-secondary);">
                                    <input type="checkbox" name="variants[{{ $variant->id }}][is_active]" value="1" {{ $variant->is_active ? 'checked' : '' }}> Active
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="eos-row-type" style="margin-top:4px;">Variants are generated from colors and sizes. Save once after adding colors/sizes, then edit variant stock/SKU/price.</div>
                </div>
            @endif

            <div style="display:flex;gap:8px;">
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ isset($product) ? 'Update' : 'Add' }} Product</button>
                <a href="{{ route('tenant.catalog') }}" class="eos-btn eos-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
