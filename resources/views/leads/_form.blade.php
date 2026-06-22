<div class="eos-card" style="margin-bottom:14px;">
    <div class="eos-card-header"><div class="eos-card-title">Contact Information</div></div>
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Name *</label>
            <input type="text" name="name" value="{{ old('name', $lead->name) }}" class="eos-input">
            @error('name') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Business Name</label>
            <input type="text" name="business_name" value="{{ old('business_name', $lead->business_name) }}" class="eos-input">
            @error('business_name') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $lead->email) }}" class="eos-input">
            @error('email') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}" class="eos-input">
            @error('phone') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="eos-card" style="margin-bottom:14px;">
    <div class="eos-card-header"><div class="eos-card-title">Project Details</div></div>
    <div class="eos-form-grid">
        <div class="eos-field">
            <label class="eos-label">Project Type</label>
            <select name="project_type" class="eos-select">
                <option value="">— Select —</option>
                @foreach (\App\Models\Lead::PROJECT_TYPES as $val => $label)
                    <option value="{{ $val }}" @selected(old('project_type', $lead->project_type) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('project_type') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Timeline</label>
            <select name="timeline" class="eos-select">
                <option value="">— Select —</option>
                @foreach (\App\Models\Lead::TIMELINES as $val => $label)
                    <option value="{{ $val }}" @selected(old('timeline', $lead->timeline) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('timeline') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Budget Min (₹)</label>
            <input type="number" step="0.01" min="0" name="budget_min" value="{{ old('budget_min', $lead->budget_min) }}" class="eos-input">
            @error('budget_min') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Budget Max (₹)</label>
            <input type="number" step="0.01" min="0" name="budget_max" value="{{ old('budget_max', $lead->budget_max) }}" class="eos-input">
            @error('budget_max') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Source</label>
            <select name="source" class="eos-select">
                <option value="">— Select —</option>
                @foreach (\App\Models\Lead::SOURCES as $val => $label)
                    <option value="{{ $val }}" @selected(old('source', $lead->source) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('source') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field">
            <label class="eos-label">Status *</label>
            <select name="status" class="eos-select">
                @foreach (\App\Models\Lead::STATUSES as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $lead->status ?? 'new') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Description</label>
            <textarea name="description" class="eos-textarea" style="min-height:80px;">{{ old('description', $lead->description) }}</textarea>
            @error('description') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        <div class="eos-field full">
            <label class="eos-label">Features / Requirements</label>
            <textarea name="features" class="eos-textarea" style="min-height:80px;">{{ old('features', $lead->features) }}</textarea>
            @error('features') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="eos-card" style="margin-bottom:14px;">
    <div class="eos-card-header"><div class="eos-card-title">Internal Notes & Conversion</div></div>
    <div class="eos-form-grid">
        <div class="eos-field full">
            <label class="eos-label">Internal Notes</label>
            <textarea name="notes" class="eos-textarea" style="min-height:80px;">{{ old('notes', $lead->notes) }}</textarea>
            @error('notes') <div class="eos-error">{{ $message }}</div> @enderror
        </div>
        @isset($clients)
            <div class="eos-field full">
                <label class="eos-label">Link to Existing Client</label>
                <select name="converted_client_id" class="eos-select">
                    <option value="">— Not converted —</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('converted_client_id', $lead->converted_client_id) == $client->id)>
                            {{ $client->name }} {{ $client->business_name ? '(' . $client->business_name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">Or use the <strong>Convert to Client</strong> button on the lead detail page to auto-create a new client.</div>
                @error('converted_client_id') <div class="eos-error">{{ $message }}</div> @enderror
            </div>
        @endisset
    </div>
</div>

<div class="eos-actions" style="margin-top:14px;">
    <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
    <a href="{{ route('leads.show', $lead) }}" class="eos-btn eos-btn-secondary">Cancel</a>
</div>
