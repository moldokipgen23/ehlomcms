@extends('layouts.app')

@section('title', $addon ? 'Edit Add-on' : 'New Add-on')

@section('subtitle', 'Set the name, description, and price clients see in the Marketplace')

@section('content')
<div style="max-width:560px;">
    <form method="POST" action="{{ $addon ? route('addon-products.update', $addon) : route('addon-products.store') }}">
        @csrf
        @if ($addon) @method('PUT') @endif

        <div class="eos-card" style="margin-bottom:14px;">
            <div style="padding:16px;">
                <div class="eos-form-grid">
                    <div class="eos-field full">
                        <label class="eos-label">Name *</label>
                        <input type="text" name="name" value="{{ old('name', $addon->name ?? '') }}" class="eos-input" required>
                        @error('name') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field full">
                        <label class="eos-label">Description</label>
                        <textarea name="description" class="eos-input" rows="2">{{ old('description', $addon->description ?? '') }}</textarea>
                        @error('description') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Price (₹/month) *</label>
                        <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $addon->price ?? '') }}" class="eos-input" required>
                        @error('price') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Icon (Tabler icon class)</label>
                        <input type="text" name="icon" value="{{ old('icon', $addon->icon ?? 'ti-puzzle') }}" class="eos-input" placeholder="e.g. ti-brand-whatsapp">
                        @error('icon') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field full">
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                            <input type="checkbox" name="active" value="1" {{ old('active', $addon->active ?? true) ? 'checked' : '' }}>
                            Visible to clients in the Marketplace
                        </label>
                    </div>
                    <div class="eos-field full">
                        <label class="eos-label">Business Types</label>
                        <div style="font-size:11px;color:var(--text-dim);margin-bottom:8px;">Leave all unchecked to show this add-on under every business type.</div>
                        <div style="display:flex;flex-wrap:wrap;gap:12px;">
                            @php $selected = old('business_types', $addon->business_types ?? []); @endphp
                            @foreach ($businessTypes as $typeKey => $type)
                                <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                                    <input type="checkbox" name="business_types[]" value="{{ $typeKey }}" {{ in_array($typeKey, $selected) ? 'checked' : '' }}>
                                    {{ $type['label'] }}
                                </label>
                            @endforeach
                        </div>
                        @error('business_types') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:8px;">
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $addon ? 'Save Changes' : 'Create Add-on' }}</button>
            <a href="{{ route('addon-marketplace.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
