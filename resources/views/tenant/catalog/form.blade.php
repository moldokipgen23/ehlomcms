@extends('tenant.layouts.dashboard')

@section('title', isset($product) ? 'Edit Product' : 'Add Product')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:640px;">
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

            <div class="eos-field">
                <label class="eos-label">Category</label>
                <input type="text" name="category" value="{{ old('category', $product->category ?? '') }}" class="eos-input" placeholder="e.g. Electronics, Clothing, Food">
            </div>

            <div class="eos-field">
                <label class="eos-label">Description</label>
                <textarea name="description" rows="4" class="eos-input" style="resize:vertical;">{{ old('description', $product->description ?? '') }}</textarea>
            </div>

            <div class="eos-field">
                <label class="eos-label">Photo</label>
                @if (isset($product) && $product->photo)
                    <div style="margin-bottom:8px;">
                        <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}" style="max-height:100px;border-radius:8px;border:1px solid var(--border);">
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*" class="eos-input">
                <div class="eos-row-type" style="margin-top:4px;">JPEG, PNG, WebP. Max 5MB.</div>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ isset($product) ? 'Update' : 'Add' }} Product</button>
                <a href="{{ route('tenant.catalog') }}" class="eos-btn eos-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
