@extends('tenant.layouts.dashboard')

@section('title', 'Store SEO')
@section('subtitle', 'Search metadata, social previews, and product SEO templates')

@section('content')
<form method="POST" action="{{ route('tenant.seo.update') }}" class="store-module-shell">
    @csrf

    <section class="store-module-hero">
        <div>
            <div class="store-module-kicker">Growth Tools</div>
            <div class="store-module-title">SEO booster</div>
            <div class="store-module-copy">Manage the store’s search title, meta description, keywords, social preview text, and reusable product/collection title templates.</div>
        </div>
        <div class="store-module-stats">
            <div class="store-mini-stat"><strong>{{ !empty($settings['seo_title']) ? 'Set' : 'Empty' }}</strong><span>Meta title</span></div>
            <div class="store-mini-stat"><strong>{{ !empty($settings['seo_description']) ? 'Set' : 'Empty' }}</strong><span>Description</span></div>
            <div class="store-mini-stat"><strong>{{ ($settings['seo_indexing'] ?? true) ? 'On' : 'Off' }}</strong><span>Indexing</span></div>
        </div>
    </section>

    <section class="store-panel-clean" style="padding:20px;">
        <div class="store-panel-clean-title" style="font-size:16px;margin-bottom:14px;">Homepage SEO</div>
        <div class="eos-field">
            <label class="eos-label">SEO Title</label>
            <input name="seo_title" maxlength="70" value="{{ old('seo_title', $settings['seo_title'] ?? '') }}" class="eos-input" placeholder="Jem Designs | Premium Handmade Collections">
        </div>
        <div class="eos-field">
            <label class="eos-label">Meta Description</label>
            <textarea name="seo_description" maxlength="170" rows="3" class="eos-input" placeholder="Short search result description for the store...">{{ old('seo_description', $settings['seo_description'] ?? '') }}</textarea>
        </div>
        <div class="eos-field">
            <label class="eos-label">Keywords</label>
            <input name="seo_keywords" value="{{ old('seo_keywords', $settings['seo_keywords'] ?? '') }}" class="eos-input" placeholder="jewellery, handmade, gifts, premium design">
        </div>
        <label style="display:flex;gap:10px;align-items:center;color:var(--text-secondary);font-weight:700;font-size:13px;">
            <input type="checkbox" name="seo_indexing" value="1" {{ old('seo_indexing', $settings['seo_indexing'] ?? true) ? 'checked' : '' }}>
            Allow search engines to index this store
        </label>
    </section>

    <section class="store-panel-clean" style="padding:20px;">
        <div class="store-panel-clean-title" style="font-size:16px;margin-bottom:14px;">Social Preview</div>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
            <div class="eos-field">
                <label class="eos-label">Open Graph Title</label>
                <input name="seo_og_title" value="{{ old('seo_og_title', $settings['seo_og_title'] ?? '') }}" class="eos-input" placeholder="Jem Designs">
            </div>
            <div class="eos-field">
                <label class="eos-label">Open Graph Description</label>
                <input name="seo_og_description" value="{{ old('seo_og_description', $settings['seo_og_description'] ?? '') }}" class="eos-input" placeholder="Premium store preview text for WhatsApp/Facebook">
            </div>
        </div>
    </section>

    <section class="store-panel-clean" style="padding:20px;">
        <div class="store-panel-clean-title" style="font-size:16px;margin-bottom:14px;">Product SEO Templates</div>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
            <div class="eos-field">
                <label class="eos-label">Product Title Template</label>
                <input name="seo_product_template" value="{{ old('seo_product_template', $settings['seo_product_template'] ?? '') }}" class="eos-input" placeholder="{product} | Jem Designs">
            </div>
            <div class="eos-field">
                <label class="eos-label">Collection Title Template</label>
                <input name="seo_collection_template" value="{{ old('seo_collection_template', $settings['seo_collection_template'] ?? '') }}" class="eos-input" placeholder="{collection} Collection | Jem Designs">
            </div>
        </div>
    </section>

    <div style="text-align:right;">
        <button type="submit" class="eos-btn eos-btn-primary" style="padding:12px 24px;"><i class="ti ti-device-floppy"></i> Save SEO Settings</button>
    </div>
</form>
@endsection
