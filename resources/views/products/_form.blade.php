<div class="eos-card">
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Name *</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="eos-input">
            @error('name') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Category *</label>
            <select name="category" class="eos-select">
                @foreach (\App\Models\Product::CATEGORIES as $val => $label)
                    <option value="{{ $val }}" @selected(old('category', $product->category ?? 'custom') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">Groups this item under Domain, Hosting, or Custom in the catalog.</div>
            @error('category') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Type / Subtype *</label>
            <input type="text" name="type" value="{{ old('type', $product->type) }}" class="eos-input" list="product-types" placeholder="e.g. Shared Hosting, Managed Hosting">
            <datalist id="product-types">
                @foreach (\App\Http\Controllers\ProductController::TYPES as $type)
                    <option value="{{ $type }}">
                @endforeach
            </datalist>
            <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">The specific subtype — pick a suggestion or type your own.</div>
            @error('type') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Price (₹) *</label>
            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="eos-input">
            @error('price') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Billing Cycle *</label>
            <select name="billing_cycle" class="eos-select">
                @foreach (['monthly', 'quarterly', 'yearly'] as $c)
                    <option value="{{ $c }}" @selected(old('billing_cycle', $product->billing_cycle ?? 'monthly') === $c)>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
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
        <a href="{{ route('products.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
    </div>
</div>
