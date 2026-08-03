@extends('tenant.layouts.dashboard')

@section('title', isset($collection) ? 'Edit Collection' : 'Add Collection')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:720px;">
        <div class="eos-card-header">
            <div class="eos-card-title">{{ isset($collection) ? 'Edit Collection' : 'Add Collection' }}</div>
        </div>
        <form method="POST" action="{{ isset($collection) ? route('tenant.collections.update', $collection->id) : route('tenant.collections.store') }}" enctype="multipart/form-data" style="padding:16px;">
            @csrf
            @if (isset($collection)) @method('PUT') @endif
            <div class="eos-field">
                <label class="eos-label">Collection Name *</label>
                <input type="text" name="name" value="{{ old('name', $collection->name ?? '') }}" class="eos-input" required>
            </div>
            <div class="eos-field">
                <label class="eos-label">Description</label>
                <textarea name="description" rows="4" class="eos-input">{{ old('description', $collection->description ?? '') }}</textarea>
            </div>
            <div class="eos-field">
                <label class="eos-label">Cover Image</label>
                @if (isset($collection) && $collection->cover_image)
                    <img src="{{ Storage::url($collection->cover_image) }}" alt="" style="max-height:100px;border-radius:8px;border:1px solid var(--border);margin-bottom:8px;">
                @endif
                <input type="file" name="cover_image" accept="image/*" class="eos-input">
            </div>
            <div class="eos-field">
                <label class="eos-label">Sort Order</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $collection->sort_order ?? 0) }}" class="eos-input">
            </div>
            <label style="display:flex;gap:6px;align-items:center;font-size:12px;color:var(--text-secondary);margin-bottom:16px;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $collection->is_active ?? true) ? 'checked' : '' }}> Active
            </label>
            <div style="display:flex;gap:8px;">
                <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save Collection</button>
                <a href="{{ route('tenant.collections') }}" class="eos-btn eos-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
