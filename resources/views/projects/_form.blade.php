@php
    $catalog = ($products ?? collect())->map(fn ($p) => ['id' => $p->id, 'price' => (float) $p->price]);
    $assigned = old('products', $project->exists
        ? $project->products->map(fn ($p) => [
            'product_id' => (string) $p->id,
            'quantity' => (float) $p->pivot->quantity,
            'unit_price' => (float) $p->pivot->unit_price,
          ])->values()->toArray()
        : []);
@endphp
<div class="eos-card">
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Client *</label>
            <select name="client_id" class="eos-select">
                <option value="">— Select client —</option>
                @foreach ($clients as $c)
                    <option value="{{ $c->id }}" @selected(old('client_id', $project->client_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('client_id') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Project Title *</label>
            <input type="text" name="title" value="{{ old('title', $project->title) }}" class="eos-input">
            @error('title') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Start Date</label>
            <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="eos-input">
            @error('start_date') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Delivery Date</label>
            <input type="date" name="delivery_date" value="{{ old('delivery_date', $project->delivery_date?->format('Y-m-d')) }}" class="eos-input">
            @error('delivery_date') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Status *</label>
            <select name="status" class="eos-select">
                @foreach (['pending', 'in_progress', 'review', 'completed'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $project->status ?? 'pending') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            @error('status') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Description</label>
            <textarea name="description" class="eos-textarea">{{ old('description', $project->description) }}</textarea>
            @error('description') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Internal Notes</label>
            <textarea name="notes" class="eos-textarea">{{ old('notes', $project->notes) }}</textarea>
            @error('notes') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

{{-- ── INCLUDED PRODUCTS & SERVICES ── --}}
<div class="eos-card" style="margin-top:14px;"
     x-data="{
        catalog: {{ Js::from($catalog) }},
        rows: {{ Js::from($assigned) }},
        addRow() { this.rows.push({ product_id: '', quantity: 1, unit_price: 0 }); },
        removeRow(i) { this.rows.splice(i, 1); },
        priceOf(id) { const p = this.catalog.find(c => c.id == id); return p ? p.price : 0; },
        onPick(row) { row.unit_price = this.priceOf(row.product_id) || row.unit_price; },
        lineTotal(row) { return (parseFloat(row.quantity) || 0) * (parseFloat(row.unit_price) || 0); },
        get total() { return this.rows.reduce((s, r) => s + this.lineTotal(r), 0); },
        money(n) { return '₹' + (n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
     }">
    <div class="eos-card-header"><div class="eos-card-title">Included Products &amp; Services</div></div>
    <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:10px;">
        Products covered by this project. Selecting a product fills its catalog price — adjust the quantity or unit price as needed.
    </p>
    <table class="eos-table" style="margin-bottom:10px;">
        <thead>
            <tr><th style="width:45%;">Product / Service</th><th>Qty</th><th>Unit Price</th><th>Line Total</th><th></th></tr>
        </thead>
        <tbody>
            <template x-for="(row, i) in rows" :key="i">
                <tr>
                    <td>
                        <select class="eos-select" :name="`products[${i}][product_id]`" x-model="row.product_id" @change="onPick(row)">
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
                    <td><input type="number" step="0.01" min="0" class="eos-input" style="width:80px;" :name="`products[${i}][quantity]`" x-model="row.quantity"></td>
                    <td><input type="number" step="0.01" min="0" class="eos-input" style="width:120px;" :name="`products[${i}][unit_price]`" x-model="row.unit_price"></td>
                    <td><span class="eos-amt" x-text="money(lineTotal(row))"></span></td>
                    <td><button type="button" class="eos-icon-action del" @click="removeRow(i)"><i class="ti ti-x"></i></button></td>
                </tr>
            </template>
            <tr x-show="!rows.length">
                <td colspan="5"><div class="eos-empty">No products added yet.</div></td>
            </tr>
        </tbody>
    </table>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
        <button type="button" class="eos-btn eos-btn-secondary" @click="addRow()"><i class="ti ti-plus"></i> Add Product</button>
        <div style="font-weight:700;font-size:14px;">Project Total: <span class="eos-amt" x-text="money(total)"></span></div>
    </div>
</div>

<div class="eos-actions" style="margin-top:14px;">
    <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
    <a href="{{ route('projects.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
</div>
