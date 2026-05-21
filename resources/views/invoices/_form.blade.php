@php
    $existingItems = old('items', $invoice->exists
        ? $invoice->items->map(fn ($i) => [
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
          ])->toArray()
        : [['description' => '', 'quantity' => 1, 'unit_price' => 0]]);
@endphp
@php
    $gstDefault = $invoice->exists ? ((float) $invoice->tax_rate > 0) : true;
    $rateDefault = $invoice->exists && (float) $invoice->tax_rate > 0 ? (float) $invoice->tax_rate : 18;
@endphp
<div class="eos-card"
     x-data="{
        applyGst: {{ old('apply_gst', $gstDefault) ? 'true' : 'false' }},
        taxRate: {{ (float) old('tax_rate', $rateDefault) }},
        items: {{ Js::from($existingItems) }},
        addRow() { this.items.push({ description: '', quantity: 1, unit_price: 0 }); },
        removeRow(i) { this.items.splice(i, 1); if (!this.items.length) this.addRow(); },
        lineTotal(it) { return (parseFloat(it.quantity) || 0) * (parseFloat(it.unit_price) || 0); },
        get subtotal() { return this.items.reduce((s, it) => s + this.lineTotal(it), 0); },
        get taxAmount() { return this.applyGst ? this.subtotal * (parseFloat(this.taxRate) || 0) / 100 : 0; },
        get total() { return this.subtotal + this.taxAmount; },
        money(n) { return '₹' + (n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
     }">
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Client *</label>
            <select name="client_id" class="eos-select">
                <option value="">— Select client —</option>
                @foreach ($clients as $c)
                    <option value="{{ $c->id }}" @selected(old('client_id', $invoice->client_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('client_id') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Due Date</label>
            <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}" class="eos-input">
            @error('due_date') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        @if ($invoice->exists)
            <div class="eos-field">
                <label class="eos-label">Status</label>
                <select name="status" class="eos-select">
                    @foreach (['draft', 'unpaid', 'paid', 'overdue'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $invoice->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <label class="eos-label" style="margin-top:8px;">Line Items *</label>
    <table class="eos-table" style="margin-bottom:10px;">
        <thead>
            <tr><th style="width:50%;">Description</th><th>Qty</th><th>Unit Price</th><th>Line Total</th><th></th></tr>
        </thead>
        <tbody>
            <template x-for="(item, i) in items" :key="i">
                <tr>
                    <td><input type="text" class="eos-input" :name="`items[${i}][description]`" x-model="item.description" placeholder="Item description"></td>
                    <td><input type="number" step="0.01" min="0" class="eos-input" style="width:80px;" :name="`items[${i}][quantity]`" x-model="item.quantity"></td>
                    <td><input type="number" step="0.01" min="0" class="eos-input" style="width:110px;" :name="`items[${i}][unit_price]`" x-model="item.unit_price"></td>
                    <td><span class="eos-amt" x-text="money(lineTotal(item))"></span></td>
                    <td><button type="button" class="eos-icon-action del" @click="removeRow(i)"><i class="ti ti-x"></i></button></td>
                </tr>
            </template>
        </tbody>
    </table>
    <button type="button" class="eos-btn eos-btn-secondary" @click="addRow()"><i class="ti ti-plus"></i> Add Line Item</button>
    @error('items') <div class="eos-error">{{ $message }}</div> @enderror

    <div style="display:flex;justify-content:flex-end;margin-top:16px;">
        <div style="width:300px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:12.5px;color:var(--text-secondary);">
                <span>Apply GST</span>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                    <input type="hidden" name="apply_gst" value="0">
                    <input type="checkbox" name="apply_gst" value="1" x-model="applyGst">
                    <span x-text="applyGst ? 'Yes' : 'No'"></span>
                </label>
            </div>
            <div x-show="applyGst" style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:12.5px;color:var(--text-secondary);">
                <span>GST Rate (%)</span>
                <input type="number" step="0.01" min="0" max="100" name="tax_rate" x-model="taxRate" class="eos-input" style="width:120px;">
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;color:var(--text-secondary);">
                <span>Subtotal</span><span class="eos-amt" x-text="money(subtotal)"></span>
            </div>
            <div x-show="applyGst" style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;color:var(--text-secondary);">
                <span>GST (<span x-text="(parseFloat(taxRate) || 0)"></span>%)</span>
                <span class="eos-amt" x-text="money(taxAmount)"></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--border);font-weight:700;">
                <span>Total</span><span class="eos-amt" style="font-size:14px;" x-text="money(total)"></span>
            </div>
        </div>
    </div>

    <div class="eos-field full" style="margin-top:10px;">
        <label class="eos-label">Notes</label>
        <textarea name="notes" class="eos-textarea">{{ old('notes', $invoice->notes) }}</textarea>
    </div>

    @if ($emailOption ?? false)
        <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary);margin:14px 0 4px;cursor:pointer;">
            <input type="hidden" name="notify_client" value="0">
            <input type="checkbox" name="notify_client" value="1" {{ old('notify_client') ? 'checked' : '' }}>
            <span><i class="ti ti-mail"></i> Also email this invoice to the client now (optional)</span>
        </label>
        <div style="font-size:11px;color:var(--text-dim);margin-bottom:6px;">
            Off by default — invoices are sent manually. Tick this only if you want it emailed immediately.
            You can always send it later with the “Send Email” button on the invoice page.
        </div>
    @endif

    <div class="eos-actions">
        <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
        <a href="{{ route('invoices.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
    </div>
</div>
