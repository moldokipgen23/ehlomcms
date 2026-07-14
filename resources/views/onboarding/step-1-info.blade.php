@extends('layouts.app')

@section('title', 'Onboarding: Business Info')
@section('subtitle', 'Step 1 of 5 — Confirm business details for ' . $tenant->name)

@section('content')
<div style="max-width:600px;">
    @include('onboarding._progress', ['current' => 1])

    <div class="eos-card">
        <div class="eos-card-header">
            <div class="eos-card-title"><i class="ti ti-building"></i> Business Information</div>
        </div>
        <div class="eos-card-body" style="padding:20px;">
            <form method="POST" action="{{ route('onboarding.update', ['tenant' => $tenant, 'step' => 'info']) }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:14px;">
                    <div class="eos-field">
                        <label class="eos-label">Site Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" class="eos-input" required>
                        @error('name') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Contact Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email ?? $client->email ?? '') }}" class="eos-input">
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Contact Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone ?? $client->phone ?? '') }}" class="eos-input">
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $tenant->whatsapp_number ?? $client->whatsapp ?? '') }}" class="eos-input">
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">About Text</label>
                        <textarea name="about_text" class="eos-input" rows="3">{{ old('about_text', $tenant->about_text ?? $client->notes ?? '') }}</textarea>
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Address</label>
                        <input type="text" name="contact_address" value="{{ old('contact_address', $tenant->contact_address ?? $client->address ?? '') }}" class="eos-input">
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Business Hours</label>
                        <input type="text" name="contact_hours" value="{{ old('contact_hours', $tenant->contact_hours ?? '') }}" class="eos-input" placeholder="e.g. Mon-Sat 9AM-6PM">
                    </div>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                    <a href="{{ route('onboarding.skip', $tenant) }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;">Skip to Dashboard</a>
                    <button type="submit" class="eos-btn eos-btn-primary" style="padding:10px 20px;">Continue <i class="ti ti-arrow-right"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
