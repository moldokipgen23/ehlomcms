@php
    $showStatus = $showStatus ?? false;
    $catalog = ($products ?? collect())->map(fn ($p) => ['id' => $p->id, 'price' => (float) $p->price]);
    $assigned = old('products', $client->exists
        ? $client->products->map(fn ($p) => [
            'product_id' => (string) $p->id,
            'custom_price' => $p->pivot->custom_price !== null ? (float) $p->pivot->custom_price : '',
          ])->values()->toArray()
        : []);
@endphp
<div class="eos-card">
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Name *</label>
            <input type="text" name="name" value="{{ old('name', $client->name) }}" class="eos-input">
            @error('name') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Business Name</label>
            <input type="text" name="business_name" value="{{ old('business_name', $client->business_name) }}" class="eos-input">
            @error('business_name') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Phone *</label>
            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="eos-input">
            @error('phone') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">WhatsApp Number</label>
            <input type="text" name="whatsapp" value="{{ old('whatsapp', $client->whatsapp) }}" class="eos-input" placeholder="e.g. 919876543210">
            @error('whatsapp') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $client->email) }}" class="eos-input">
            @error('email') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        @if ($showStatus)
            <div class="eos-field">
                <label class="eos-label">Status</label>
                <select name="status" class="eos-select">
                    @foreach (['active', 'inactive', 'suspended'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $client->status ?? 'active') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                @error('status') <div class="eos-error">{{ $message }}</div> @enderror
            </div>
        @endif
        <div class="eos-field full">
            <label class="eos-label">Address</label>
            <input type="text" name="address" value="{{ old('address', $client->address) }}" class="eos-input">
            @error('address') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Notes</label>
            <textarea name="notes" class="eos-textarea">{{ old('notes', $client->notes) }}</textarea>
            @error('notes') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
    </div>
    @unless ($showStatus)
        <div style="font-size:11.5px;color:var(--text-dim);">
            <i class="ti ti-info-circle"></i> Only Name and Phone are required. You can fill the rest later from the client profile.
        </div>
    @endunless
</div>

{{-- ── ASSIGNED PRODUCTS & SERVICES ── --}}
<div class="eos-card" style="margin-top:14px;"
     x-data="{
        catalog: {{ Js::from($catalog) }},
        rows: {{ Js::from($assigned) }},
        addRow() { this.rows.push({ product_id: '', custom_price: '' }); },
        removeRow(i) { this.rows.splice(i, 1); },
        priceOf(id) { const p = this.catalog.find(c => c.id == id); return p ? p.price : 0; }
     }">
    <div class="eos-card-header"><div class="eos-card-title">Assigned Products &amp; Services</div></div>
    <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:10px;">
        Products this client has purchased. Leave the price blank to use the catalog price, or set a custom price for this client.
    </p>
    <table class="eos-table" style="margin-bottom:10px;">
        <thead>
            <tr><th style="width:60%;">Product / Service</th><th>Price (₹)</th><th></th></tr>
        </thead>
        <tbody>
            <template x-for="(row, i) in rows" :key="i">
                <tr>
                    <td>
                        <select class="eos-select" :name="`products[${i}][product_id]`" x-model="row.product_id">
                            <option value="">— Select product —</option>
                            @foreach (($products ?? collect())->groupBy('category') as $cat => $items)
                                <optgroup label="{{ \App\Models\Product::CATEGORIES[$cat] ?? ucfirst($cat) }}">
                                    @foreach ($items as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} — ₹{{ number_format($p->price, 0) }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" class="eos-input" style="width:140px;"
                               :name="`products[${i}][custom_price]`" x-model="row.custom_price"
                               :placeholder="row.product_id ? ('Default ₹' + priceOf(row.product_id)) : 'Price'">
                    </td>
                    <td><button type="button" class="eos-icon-action del" @click="removeRow(i)"><i class="ti ti-x"></i></button></td>
                </tr>
            </template>
            <tr x-show="!rows.length">
                <td colspan="3"><div class="eos-empty">No products assigned yet.</div></td>
            </tr>
        </tbody>
    </table>
    <button type="button" class="eos-btn eos-btn-secondary" @click="addRow()"><i class="ti ti-plus"></i> Add Product</button>
</div>

<div class="eos-actions" style="margin-top:14px;">
    <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
    <a href="{{ route('clients.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
</div>
