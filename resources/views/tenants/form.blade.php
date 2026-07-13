@extends('layouts.app')

@section('title', 'Create Tenant')

@section('subtitle', 'Onboard a new client tenant site')

@section('content')
<div style="max-width:600px;">
    <form method="POST" action="{{ route('tenants.store') }}">
        @csrf

        <div class="eos-card" style="margin-bottom:14px;">
            <div class="eos-card-header">
                <div class="eos-card-title">Tenant Details</div>
            </div>
            <div style="padding:16px;">
                <div class="eos-form-grid">
                    <div class="eos-field">
                        <label class="eos-label">Subdomain *</label>
                        <input type="text" name="subdomain" value="{{ old('subdomain') }}" class="eos-input" required placeholder="e.g. myclient">
                        @error('subdomain') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Business Name *</label>
                        <input type="text" name="name" value="{{ old('name', $prefillClient?->business_name ?: $prefillClient?->name) }}" class="eos-input" required>
                        @error('name') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Site Type *</label>
                        <select name="site_type" class="eos-select" required>
                            <option value="info" @selected(old('site_type') === 'info')>Info / Portfolio</option>
                            <option value="shopping" @selected(old('site_type') === 'shopping')>Shopping / Store</option>
                            <option value="restaurant" @selected(old('site_type') === 'restaurant')>Restaurant</option>
                        </select>
                        @error('site_type') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field full">
                        <label class="eos-label">Template</label>
                        <x-theme-gallery :themes="$themes" :selected="old('template_id')" />
                        @error('template_id') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Action Type</label>
                        <select name="action_type" class="eos-select">
                            <option value="">— None —</option>
                            <option value="whatsapp" @selected(old('action_type') === 'whatsapp')>WhatsApp</option>
                            <option value="razorpay" @selected(old('action_type') === 'razorpay')>Razorpay</option>
                        </select>
                        @error('action_type') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Plan</label>
                        <input type="text" name="plan" value="{{ old('plan') }}" class="eos-input" placeholder="e.g. Basic, Pro">
                        @error('plan') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="eos-card" style="margin-bottom:14px;">
            <div class="eos-card-header">
                <div class="eos-card-title">Modules</div>
            </div>
            <div style="padding:16px;">
                <div class="eos-page-sub" style="margin-bottom:12px;">
                    Enable dashboard sections for this tenant.
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach ($modules as $key => $m)
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--text-secondary);">
                            <input type="checkbox" name="modules[]" value="{{ $key }}"
                                   {{ in_array($key, old('modules', [])) ? 'checked' : '' }}
                                   style="accent-color:var(--accent-blue);width:16px;height:16px;">
                            <i class="ti {{ $m['icon'] }}" style="font-size:16px;color:var(--text-muted);"></i>
                            {{ $m['label'] }}
                            <span style="font-size:10px;color:var(--text-dim);margin-left:4px;">— {{ $m['description'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('modules') <div class="eos-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="eos-card" style="margin-bottom:14px;">
            <div class="eos-card-header">
                <div class="eos-card-title">Owner Login</div>
            </div>
            <div style="padding:16px;">
                <div class="eos-page-sub" style="margin-bottom:12px;">
                    Creates the client's first dashboard login. There is no public
                    self-registration - a login is only ever created here by you.
                    The generated password is shown once after creation; pass it
                    to the client yourself (WhatsApp/email).
                </div>
                <div class="eos-form-grid">
                    <div class="eos-field">
                        <label class="eos-label">Owner Name *</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name', $prefillClient?->name) }}" class="eos-input" required>
                        @error('owner_name') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Owner Email *</label>
                        <input type="email" name="owner_email" value="{{ old('owner_email', $prefillClient?->email) }}" class="eos-input" required>
                        @error('owner_email') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="eos-card" style="margin-bottom:14px;">
            <div class="eos-card-header">
                <div class="eos-card-title">Link to Client</div>
            </div>
            <div style="padding:16px;">
                <div class="eos-field">
                    <label class="eos-label">Existing CRM Client (optional)</label>
                    <select name="client_id" class="eos-select">
                        <option value="">— No linked client —</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id', $prefillClient?->id) == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <div class="eos-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div style="display:flex;gap:8px;">
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Create Tenant</button>
            <a href="{{ route('tenants.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection