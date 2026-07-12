@extends('tenant.layouts.dashboard')

@section('title', 'Settings')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Business Information</div>
        </div>

        <form method="POST" action="{{ route('tenant.settings') }}" style="padding:16px;">
            @csrf
            <div class="eos-field">
                <label class="eos-label">Business Name</label>
                <input type="text" name="name" value="{{ old('name', $tenant->name) }}" class="eos-input" required>
            </div>
            <div class="eos-field">
                <label class="eos-label">WhatsApp Number</label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $tenant->whatsapp_number) }}" class="eos-input" placeholder="e.g. 919876543210">
            </div>
            <div class="eos-field">
                <label class="eos-label">Contact Email</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" class="eos-input">
            </div>
            <div class="eos-field">
                <label class="eos-label">Contact Phone</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}" class="eos-input">
            </div>
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save Changes</button>
        </form>
    </div>

    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Logo</div>
        </div>
        <form method="POST" action="{{ route('tenant.settings.logo') }}" enctype="multipart/form-data" style="padding:16px;">
            @csrf
            @if ($tenant->logo)
                <div style="margin-bottom:12px;">
                    <img src="{{ Storage::url($tenant->logo) }}" alt="Logo" style="max-height:80px;border-radius:8px;border:1px solid var(--border);">
                </div>
            @endif
            <div class="eos-field">
                <label class="eos-label">Upload Logo</label>
                <input type="file" name="logo" accept="image/*" class="eos-input">
                <div class="eos-row-type" style="margin-top:4px;">JPEG, PNG, WebP. Max 2MB.</div>
            </div>
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-upload"></i> Upload Logo</button>
        </form>
    </div>

    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Banner Image</div>
        </div>
        <form method="POST" action="{{ route('tenant.settings.banner') }}" enctype="multipart/form-data" style="padding:16px;">
            @csrf
            @if ($tenant->banner_image)
                <div style="margin-bottom:12px;">
                    <img src="{{ Storage::url($tenant->banner_image) }}" alt="Banner" style="max-height:120px;width:100%;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                </div>
            @endif
            <div class="eos-field">
                <label class="eos-label">Upload Banner Image</label>
                <input type="file" name="banner" accept="image/*" class="eos-input">
                <div class="eos-row-type" style="margin-top:4px;">JPEG, PNG, WebP. Max 5MB.</div>
            </div>
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-upload"></i> Upload Banner</button>
        </form>
    </div>
</div>
@endsection
