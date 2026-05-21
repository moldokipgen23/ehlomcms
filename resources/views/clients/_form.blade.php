@php $showStatus = $showStatus ?? false; @endphp
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
        <div style="font-size:11.5px;color:var(--text-dim);margin-bottom:10px;">
            <i class="ti ti-info-circle"></i> Only Name and Phone are required. You can fill the rest later from the client profile.
        </div>
    @endunless
    <div class="eos-actions" style="margin-top:6px;">
        <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ $submit }}</button>
        <a href="{{ route('clients.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
    </div>
</div>
