<div class="eos-card">
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Name *</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="eos-input">
            @error('name') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Type *</label>
            <select name="type" class="eos-select">
                @foreach (\App\Http\Controllers\ProductController::TYPES as $type)
                    <option value="{{ $type }}" @selected(old('type', $product->type) === $type)>{{ $type }}</option>
                @endforeach
            </select>
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
