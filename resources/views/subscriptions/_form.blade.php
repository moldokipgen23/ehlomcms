@php
    $prices = $products->mapWithKeys(fn ($p) => [$p->id => (float) $p->price]);
@endphp
<div class="eos-card"
     x-data="{
        prices: {{ Js::from($prices) }},
        amount: '{{ old('renewal_amount', $subscription->renewal_amount) }}',
        productChanged(id) { if (this.prices[id] !== undefined) this.amount = this.prices[id]; }
     }">
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Client *</label>
            <select name="client_id" class="eos-select">
                <option value="">— Select client —</option>
                @foreach ($clients as $c)
                    <option value="{{ $c->id }}" @selected(old('client_id', $subscription->client_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('client_id') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Product *</label>
            <select name="product_id" class="eos-select" x-on:change="productChanged($event.target.value)">
                <option value="">— Select product —</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected(old('product_id', $subscription->product_id) == $p->id)>{{ $p->name }} (₹{{ number_format($p->price, 0) }})</option>
                @endforeach
            </select>
            @error('product_id') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Start Date *</label>
            <input type="date" name="start_date" value="{{ old('start_date', $subscription->start_date?->format('Y-m-d')) }}" class="eos-input">
            @error('start_date') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Expiry Date *</label>
            <input type="date" name="expiry_date" value="{{ old('expiry_date', $subscription->expiry_date?->format('Y-m-d')) }}" class="eos-input">
            @error('expiry_date') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Renewal Amount (₹) *</label>
            <input type="number" step="0.01" min="0" name="renewal_amount" x-model="amount" class="eos-input">
            @error('renewal_amount') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Status *</label>
            <select name="status" class="eos-select">
                @foreach (['active', 'expired', 'cancelled'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $subscription->status ?? 'active') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            @error('status') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Auto Renewal Invoice</label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12.5px;color:var(--text-secondary);">
                <input type="hidden" name="auto_invoice" value="0">
                <input type="checkbox" name="auto_invoice" value="1" @checked(old('auto_invoice', $subscription->auto_invoice))>
                Auto-generate a draft renewal invoice 30 days before expiry
            </label>
            @error('auto_invoice') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Notes</label>
            <textarea name="notes" class="eos-textarea">{{ old('notes', $subscription->notes) }}</textarea>
            @error('notes') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="eos-actions" style="margin-top:6px;">
        <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
        <a href="{{ route('subscriptions.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
    </div>
</div>
