<div class="eos-card">
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Client *</label>
            <select name="client_id" class="eos-select">
                <option value="">— Select client —</option>
                @foreach ($clients as $c)
                    <option value="{{ $c->id }}" @selected(old('client_id', $agreement->client_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('client_id') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Agreement Title *</label>
            <input type="text" name="title" value="{{ old('title', $agreement->title) }}" class="eos-input" placeholder="Website Development Agreement">
            @error('title') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Type *</label>
            <select name="type" class="eos-select">
                @foreach (['website' => 'Website', 'hosting' => 'Hosting', 'maintenance' => 'Maintenance', 'ecommerce' => 'E-commerce', 'other' => 'Other'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('type', $agreement->type ?? 'website') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Status *</label>
            <select name="status" class="eos-select">
                @foreach (['draft' => 'Draft', 'sent' => 'Sent', 'signed' => 'Signed', 'cancelled' => 'Cancelled'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $agreement->status ?? 'draft') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Amount (₹) *</label>
            <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $agreement->amount) }}" class="eos-input">
            @error('amount') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Start Date *</label>
            <input type="date" name="start_date" value="{{ old('start_date', $agreement->start_date?->format('Y-m-d')) }}" class="eos-input">
            @error('start_date') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">End Date</label>
            <input type="date" name="end_date" value="{{ old('end_date', $agreement->end_date?->format('Y-m-d')) }}" class="eos-input">
            @error('end_date') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Scope of Work</label>
            <textarea name="scope" class="eos-textarea" rows="4" placeholder="What exactly is being built / delivered…">{{ old('scope', $agreement->scope) }}</textarea>
            @error('scope') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Payment Terms</label>
            <textarea name="payment_terms" class="eos-textarea" rows="3" placeholder="e.g. 50% advance, 50% on completion">{{ old('payment_terms', $agreement->payment_terms) }}</textarea>
            @error('payment_terms') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Terms &amp; Conditions</label>
            <textarea name="terms_and_conditions" class="eos-textarea" rows="8">{{ old('terms_and_conditions', $agreement->terms_and_conditions) }}</textarea>
            @error('terms_and_conditions') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Notes</label>
            <textarea name="notes" class="eos-textarea" rows="2">{{ old('notes', $agreement->notes) }}</textarea>
            @error('notes') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="eos-actions" style="margin-top:6px;">
        <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
        <a href="{{ route('agreements.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
    </div>
</div>
