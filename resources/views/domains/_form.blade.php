<div class="eos-card">
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Client *</label>
            <select name="client_id" class="eos-select">
                <option value="">— Select client —</option>
                @foreach ($clients as $c)
                    <option value="{{ $c->id }}" @selected(old('client_id', $domain->client_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('client_id') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Domain Name *</label>
            <input type="text" name="domain_name" value="{{ old('domain_name', $domain->domain_name) }}" class="eos-input" placeholder="example.com">
            @error('domain_name') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Registrar</label>
            <input type="text" name="registrar" value="{{ old('registrar', $domain->registrar) }}" class="eos-input" placeholder="GoDaddy, Namecheap…">
            @error('registrar') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Status *</label>
            <select name="status" class="eos-select">
                @foreach (['active', 'expired', 'transferred'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $domain->status ?? 'active') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            @error('status') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Purchase Date</label>
            <input type="date" name="purchase_date" value="{{ old('purchase_date', $domain->purchase_date?->format('Y-m-d')) }}" class="eos-input">
            @error('purchase_date') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Expiry Date *</label>
            <input type="date" name="expiry_date" value="{{ old('expiry_date', $domain->expiry_date?->format('Y-m-d')) }}" class="eos-input">
            @error('expiry_date') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Renewal Cost (₹)</label>
            <input type="number" step="0.01" min="0" name="renewal_cost" value="{{ old('renewal_cost', $domain->renewal_cost) }}" class="eos-input">
            @error('renewal_cost') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Nameserver</label>
            <input type="text" name="nameserver" value="{{ old('nameserver', $domain->nameserver) }}" class="eos-input">
            @error('nameserver') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Hosting Server</label>
            <input type="text" name="hosting_server" value="{{ old('hosting_server', $domain->hosting_server) }}" class="eos-input">
            @error('hosting_server') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Hosting Plan</label>
            <input type="text" name="hosting_plan" value="{{ old('hosting_plan', $domain->hosting_plan) }}" class="eos-input">
            @error('hosting_plan') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">CloudPanel Notes</label>
            <textarea name="cloudpanel_notes" class="eos-textarea">{{ old('cloudpanel_notes', $domain->cloudpanel_notes) }}</textarea>
            @error('cloudpanel_notes') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Notes</label>
            <textarea name="notes" class="eos-textarea">{{ old('notes', $domain->notes) }}</textarea>
            @error('notes') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="eos-actions" style="margin-top:6px;">
        <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
        <a href="{{ route('infrastructure.index', ['tab' => 'registered']) }}" class="eos-btn eos-btn-secondary">Cancel</a>
    </div>
</div>
