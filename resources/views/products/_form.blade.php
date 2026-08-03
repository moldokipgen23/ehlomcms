@php
    $cat = old('category', $product->category ?? 'custom');
    $cancelUrl = $cat === 'custom' ? route('products.index') : route('infrastructure.index', ['tab' => $cat]);
@endphp
<div class="eos-card">
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Name *</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="eos-input" placeholder="e.g. Shared Hosting Basic, .com, Web Design">
            @error('name') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Category *</label>
            <select name="category" class="eos-select">
                @foreach (\App\Models\Product::CATEGORIES as $val => $label)
                    <option value="{{ $val }}" @selected($cat === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">Hosting &amp; Domain show under Domains &amp; Hosting · Custom shows under Products &amp; Services.</div>
            @error('category') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Price (₹) *</label>
            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="eos-input">
            @error('price') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Billing Cycle *</label>
            <select name="billing_cycle" class="eos-select">
                @foreach (\App\Models\Product::BILLING_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('billing_cycle', $product->billing_cycle ?? 'yearly') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">One-time products do not create renewal subscriptions. Monthly/quarterly/yearly products can have expiry and renewal invoices.</div>
            @error('billing_cycle') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Status *</label>
            <select name="status" class="eos-select">
                @foreach (['active', 'inactive'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $product->status ?? 'active') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            @error('status') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Description</label>
            <textarea name="description" class="eos-textarea">{{ old('description', $product->description) }}</textarea>
            @error('description') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="eos-actions" style="margin-top:6px;">
        <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
        <a href="{{ $cancelUrl }}" class="eos-btn eos-btn-secondary">Cancel</a>
    </div>
</div>
