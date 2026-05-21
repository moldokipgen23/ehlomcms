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
    <div class="eos-actions" style="margin-top:6px;">
        <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
        <a href="{{ route('projects.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
    </div>
</div>
