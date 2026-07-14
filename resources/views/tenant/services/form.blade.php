@extends('tenant.layouts.dashboard')

@section('title', isset($service) ? 'Edit Service' : 'Add Service')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">{{ isset($service) ? 'Edit Service' : 'Add Service' }}</div>
        </div>

        <form method="POST" action="{{ isset($service) ? route('tenant.services.update', $service->id) : route('tenant.services.store') }}" enctype="multipart/form-data" style="padding:16px;">
            @csrf
            @if (isset($service)) @method('PUT') @endif

            <div class="eos-field">
                <label class="eos-label">Service Name *</label>
                <input type="text" name="name" value="{{ old('name', $service->name ?? '') }}" class="eos-input" required>
            </div>

            <div class="eos-field">
                <label class="eos-label">Price (₹, optional)</label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $service->price ?? '') }}" class="eos-input" placeholder="Leave blank for &quot;Contact for pricing&quot;">
            </div>

            <div class="eos-field">
                <label class="eos-label">Description</label>
                <textarea name="description" rows="4" class="eos-input" style="resize:vertical;">{{ old('description', $service->description ?? '') }}</textarea>
            </div>

            <div class="eos-field">
                <label class="eos-label">Photo</label>
                @if (isset($service) && $service->photo)
                    <div style="margin-bottom:8px;">
                        <img src="{{ Storage::url($service->photo) }}" alt="{{ $service->name }}" style="max-height:100px;border-radius:8px;border:1px solid var(--border);">
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*" class="eos-input">
                <div class="eos-row-type" style="margin-top:4px;">JPEG, PNG, WebP. Max 5MB.</div>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ isset($service) ? 'Update' : 'Add' }} Service</button>
                <a href="{{ route('tenant.services') }}" class="eos-btn eos-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
