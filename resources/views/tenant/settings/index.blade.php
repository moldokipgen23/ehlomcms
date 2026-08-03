@extends('tenant.layouts.dashboard')

@section('title', 'Settings')
@section('subtitle', 'General store settings, branding, SEO, and contact details')

@section('content')
@php
    $settings = $tenant->theme_settings ?? [];
    $hasSeo = $tenant->hasModule('seo_booster');
@endphp

<style>
    .storefront-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 8px;
        border: 1px solid var(--border-card);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 10px 26px rgba(15,23,42,.05);
    }
    .storefront-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 36px;
        padding: 8px 12px;
        border: 1px solid transparent;
        border-radius: 9px;
        background: transparent;
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }
    .storefront-tab:hover {
        background: #eef4ff;
        color: #1d4ed8;
    }
    .storefront-tab.is-active {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        box-shadow: 0 10px 22px rgba(37,99,235,.18);
    }
    .storefront-panel {
        padding: 22px;
    }
    .storefront-panel-title {
        color: var(--text-primary);
        font-family: 'Syne', sans-serif;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 4px;
    }
    .storefront-panel-sub {
        color: var(--text-muted);
        font-size: 12px;
        margin-bottom: 18px;
    }
    .storefront-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .storefront-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
    @media (max-width: 760px) {
        .storefront-tabs { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 10px; }
        .storefront-tab { white-space: nowrap; }
        .storefront-grid-2,
        .storefront-grid-3 { grid-template-columns: 1fr; }
        .storefront-panel { padding: 16px; }
    }
</style>

<div x-data="{ tab: 'general' }" class="store-module-shell">
    <section class="store-module-hero">
        <div>
            <div class="store-module-kicker">Store Settings</div>
            <div class="store-module-title">Website Settings</div>
            <div class="store-module-copy">Manage the store profile, contact information, branding assets, favicon, and search settings. Theme assignment is controlled by Ehlom admin.</div>
        </div>
        <div class="store-module-stats">
            <div class="store-mini-stat"><strong>{{ strtoupper($tenant->template_id ?? 'CUSTOM') }}</strong><span>Assigned Theme</span></div>
        </div>
    </section>

    <div class="storefront-tabs">
        <button type="button" class="storefront-tab" :class="{ 'is-active': tab === 'general' }" @click="tab = 'general'"><i class="ti ti-settings"></i> General</button>
        <button type="button" class="storefront-tab" :class="{ 'is-active': tab === 'branding' }" @click="tab = 'branding'"><i class="ti ti-photo"></i> Branding</button>
        <button type="button" class="storefront-tab" :class="{ 'is-active': tab === 'contact' }" @click="tab = 'contact'"><i class="ti ti-address-book"></i> Contact</button>
        @if ($hasSeo)
            <button type="button" class="storefront-tab" :class="{ 'is-active': tab === 'seo' }" @click="tab = 'seo'"><i class="ti ti-seo"></i> SEO</button>
        @endif
    </div>

    <section x-show="tab === 'general'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">General</div>
        <div class="storefront-panel-sub">Basic store identity and assigned theme information.</div>
        <form method="POST" action="{{ route('tenant.settings') }}">
            @csrf
            <div class="storefront-grid-2">
                <div class="eos-field">
                    <label class="eos-label">Business Name</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" class="eos-input" required>
                </div>
                <div class="eos-field">
                    <label class="eos-label">Assigned Store Theme</label>
                    <div style="padding:12px 14px;border:1px solid var(--border);border-radius:8px;background:#f8fafc;color:var(--text-secondary);font-size:13px;font-weight:800;">
                        {{ strtoupper($tenant->template_id ?? 'CUSTOM') }}
                    </div>
                    <div class="eos-row-type" style="margin-top:8px;">Theme assignment is admin-controlled. Edit storefront content from Storefront.</div>
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save General Settings</button>
            </div>
        </form>
    </section>

    <section x-show="tab === 'branding'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Branding</div>
        <div class="storefront-panel-sub">Logo, banner, and browser favicon used by the dashboard and storefront.</div>
        <div class="storefront-grid-3">
            <form method="POST" action="{{ route('tenant.settings.logo') }}" enctype="multipart/form-data" class="eos-card" style="padding:16px;">
                @csrf
                <div class="eos-card-title" style="margin-bottom:12px;">Logo</div>
                @if ($tenant->logo)
                    <img src="{{ Storage::url($tenant->logo) }}" alt="Logo" style="max-height:80px;border-radius:8px;border:1px solid var(--border);margin-bottom:12px;">
                @endif
                <div class="eos-field"><label class="eos-label">Upload Logo</label><input type="file" name="logo" accept="image/*" class="eos-input"></div>
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-upload"></i> Upload Logo</button>
            </form>

            <form method="POST" action="{{ route('tenant.settings.favicon') }}" enctype="multipart/form-data" class="eos-card" style="padding:16px;">
                @csrf
                <div class="eos-card-title" style="margin-bottom:12px;">Favicon</div>
                @if (($settings['favicon'] ?? null))
                    <img src="{{ Storage::url($settings['favicon']) }}" alt="Favicon" style="width:48px;height:48px;object-fit:contain;border-radius:8px;border:1px solid var(--border);background:#fff;margin-bottom:12px;">
                @endif
                <div class="eos-field"><label class="eos-label">Upload Favicon</label><input type="file" name="favicon" accept=".ico,image/png,image/jpeg,image/webp" class="eos-input"></div>
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-upload"></i> Upload Favicon</button>
            </form>

            <form method="POST" action="{{ route('tenant.settings.banner') }}" enctype="multipart/form-data" class="eos-card" style="padding:16px;">
                @csrf
                <div class="eos-card-title" style="margin-bottom:12px;">Banner</div>
                @if ($tenant->banner_image)
                    <img src="{{ Storage::url($tenant->banner_image) }}" alt="Banner" style="max-height:90px;width:100%;object-fit:cover;border-radius:8px;border:1px solid var(--border);margin-bottom:12px;">
                @endif
                <div class="eos-field"><label class="eos-label">Upload Banner</label><input type="file" name="banner" accept="image/*" class="eos-input"></div>
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-upload"></i> Upload Banner</button>
            </form>
        </div>
    </section>

    <section x-show="tab === 'contact'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Contact</div>
        <div class="storefront-panel-sub">Store contact channels used for support, checkout, and public display.</div>
        <form method="POST" action="{{ route('tenant.settings') }}">
            @csrf
            <input type="hidden" name="name" value="{{ $tenant->name }}">
            <div class="storefront-grid-3">
                <div class="eos-field"><label class="eos-label">WhatsApp Number</label><input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $tenant->whatsapp_number) }}" class="eos-input" placeholder="e.g. 919876543210"></div>
                <div class="eos-field"><label class="eos-label">Contact Email</label><input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" class="eos-input"></div>
                <div class="eos-field"><label class="eos-label">Contact Phone</label><input type="text" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}" class="eos-input"></div>
            </div>
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save Contact Settings</button>
        </form>
    </section>

    @if ($hasSeo)
        <section x-show="tab === 'seo'" x-cloak class="store-panel-clean storefront-panel">
            <div class="storefront-panel-title">SEO</div>
            <div class="storefront-panel-sub">Search metadata, social previews, indexing, and product SEO templates.</div>
            <form method="POST" action="{{ route('tenant.seo.update') }}">
                @csrf
                <div class="eos-field"><label class="eos-label">SEO Title</label><input name="seo_title" maxlength="70" value="{{ old('seo_title', $settings['seo_title'] ?? '') }}" class="eos-input" placeholder="Jem Designs | Premium Handmade Collections"></div>
                <div class="eos-field"><label class="eos-label">SEO Description</label><textarea name="seo_description" maxlength="170" rows="3" class="eos-input" placeholder="Short search result description for the store...">{{ old('seo_description', $settings['seo_description'] ?? '') }}</textarea></div>
                <div class="eos-field"><label class="eos-label">Keywords</label><input name="seo_keywords" value="{{ old('seo_keywords', $settings['seo_keywords'] ?? '') }}" class="eos-input" placeholder="jewellery, handmade, gifts"></div>
                <label style="display:flex;gap:8px;align-items:center;font-size:12px;color:var(--text-secondary);font-weight:800;margin-bottom:16px;"><input type="checkbox" name="seo_indexing" value="1" {{ old('seo_indexing', $settings['seo_indexing'] ?? true) ? 'checked' : '' }}> Allow search engines to index this store</label>
                <div class="storefront-grid-2">
                    <div class="eos-field"><label class="eos-label">Social Preview Title</label><input name="seo_og_title" value="{{ old('seo_og_title', $settings['seo_og_title'] ?? '') }}" class="eos-input"></div>
                    <div class="eos-field"><label class="eos-label">Social Preview Description</label><input name="seo_og_description" value="{{ old('seo_og_description', $settings['seo_og_description'] ?? '') }}" class="eos-input"></div>
                    <div class="eos-field"><label class="eos-label">Product SEO Template</label><input name="seo_product_template" value="{{ old('seo_product_template', $settings['seo_product_template'] ?? '') }}" class="eos-input" placeholder="{product} | Jem Designs"></div>
                    <div class="eos-field"><label class="eos-label">Collection SEO Template</label><input name="seo_collection_template" value="{{ old('seo_collection_template', $settings['seo_collection_template'] ?? '') }}" class="eos-input" placeholder="{collection} Collection | Jem Designs"></div>
                </div>
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-device-floppy"></i> Save SEO Settings</button>
            </form>
        </section>
    @endif
</div>
@endsection
