@extends('layouts.app')
@php($editing = (bool) $source)
@php($settings = $source?->settings ?: [])
@section('title', $editing ? 'Edit Lead Source' : 'Add Lead Source')
@section('subtitle', 'Configure a source without giving the AI direct unrestricted access')
@section('content')
<form id="lead-source-form" method="POST" action="{{ $editing ? route('lead-sources.update', $source) : route('lead-sources.store') }}" class="eos-card" style="padding:20px;max-width:900px;">
    @csrf @if($editing) @method('PUT') @endif
    @if ($errors->any())
        <div class="eos-alert-bar warn" style="margin-bottom:16px;display:block;">
            <strong>Could not save this source.</strong>
            @foreach ($errors->all() as $error)
                <div style="margin-top:4px;">{{ $error }}</div>
            @endforeach
        </div>
    @endif
    <div class="eos-form-grid">
        <div class="eos-field"><label class="eos-label">Source name</label><input class="eos-input" name="name" value="{{ old('name', $source?->name) }}" placeholder="Hola Business Directory"></div>
        <div class="eos-field"><label class="eos-label">Source type</label><select id="lead-source-driver" class="eos-input" name="driver"><option value="hola" @selected(old('driver', $source?->driver ?? 'hola') === 'hola')>Hola directory</option><option value="google_places" @selected(old('driver', $source?->driver ?? 'hola') === 'google_places')>Google Places</option></select></div>
        <div class="eos-field" style="grid-column:1/-1" data-lead-source-driver="hola"><label class="eos-label">Base URL</label><input class="eos-input" type="url" name="base_url" value="{{ old('base_url', $source?->base_url) }}" placeholder="https://hola.ehlom.com"><div class="eos-help">The Hola directory endpoint. Google Places connects directly to Google and does not use this field.</div></div>
        <div class="eos-field"><label class="eos-label"><span data-lead-source-label="hola">Hola API key</span><span data-lead-source-label="google_places">Google Places API key</span></label><input class="eos-input" name="api_key" type="password" placeholder="Leave blank to keep existing" autocomplete="off"></div>
        <div class="eos-field" data-lead-source-driver="hola"><label class="eos-label">Hola bearer token</label><input class="eos-input" name="bearer_token" type="password" placeholder="Optional Hola auth" autocomplete="off"></div>
        <div class="eos-field"><label class="eos-label">Status</label><select class="eos-input" name="status"><option value="active" @selected(old('status', $source?->status ?? 'active') === 'active')>Active</option><option value="paused" @selected(old('status', $source?->status) === 'paused')>Paused</option></select></div>
        <div class="eos-field"><label class="eos-label">Default offer type</label><select class="eos-input" name="default_project_type">@foreach(['website' => 'Website', 'ecommerce' => 'E-commerce', 'webapp' => 'Web app', 'branding' => 'Branding', 'seo' => 'SEO', 'custom' => 'Custom software', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(old('default_project_type', data_get($settings, 'default_project_type', 'website')) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="eos-field"><label class="eos-label">Automatic sync</label><label style="display:flex;align-items:center;gap:8px;height:44px;color:var(--text-secondary);"><input type="checkbox" name="auto_sync" value="1" @checked(old('auto_sync', data_get($settings, 'auto_sync', false)))> Allow scheduled sync every 6 hours</label></div>
    </div>
    <div data-lead-source-driver="hola">
        <div style="margin-top:20px;border-top:1px solid var(--border);padding-top:16px;"><div class="eos-card-title" style="margin-bottom:6px;">Hola settings</div><div class="eos-row-type">The endpoint should return a JSON list or a response containing <code>data</code>, <code>businesses</code>, or <code>results</code>.</div></div>
        <div class="eos-form-grid" style="margin-top:12px;">
        <div class="eos-field"><label class="eos-label">Businesses path</label><input class="eos-input" name="businesses_path" value="{{ old('businesses_path', data_get($settings, 'businesses_path', 'api/v1/businesses')) }}" placeholder="api/v1/businesses"></div>
        <div class="eos-field"><label class="eos-label">Per page</label><input class="eos-input" type="number" name="per_page" value="{{ old('per_page', data_get($settings, 'per_page', 50)) }}" min="10" max="100"></div>
        <div class="eos-field"><label class="eos-label">Maximum pages per sync</label><input class="eos-input" type="number" name="max_pages" value="{{ old('max_pages', data_get($settings, 'max_pages', 5)) }}" min="1" max="20"></div>
        </div>
    </div>
    <div data-lead-source-driver="google_places">
        <div style="margin-top:20px;border-top:1px solid var(--border);padding-top:16px;"><div class="eos-card-title" style="margin-bottom:6px;">Google Places settings</div><div class="eos-row-type">Google connects directly to the official Places API. Add one search query per line, such as <code>schools in Churachandpur</code>.</div></div>
        <div class="eos-form-grid" style="margin-top:12px;">
        <div class="eos-field full"><label class="eos-label">Search queries</label><textarea class="eos-textarea" name="query" placeholder="schools in Churachandpur&#10;restaurants in Imphal">{{ old('query', implode("\n", (array) data_get($settings, 'queries', []))) }}</textarea></div>
        <div class="eos-field"><label class="eos-label">Region code</label><input class="eos-input" name="region_code" value="{{ old('region_code', data_get($settings, 'region_code', 'IN')) }}" placeholder="IN"></div>
        <div class="eos-field"><label class="eos-label">Results per query</label><input class="eos-input" type="number" name="page_size" value="{{ old('page_size', data_get($settings, 'page_size', 20)) }}" min="1" max="20"></div>
        </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px;"><a class="eos-btn" href="{{ route('lead-sources.index') }}">Cancel</a><button id="lead-source-submit" type="button" class="eos-btn eos-btn-primary" onclick="this.disabled=true; this.querySelector('span').textContent='{{ $editing ? 'Saving...' : 'Adding source...' }}'; HTMLFormElement.prototype.submit.call(document.getElementById('lead-source-form')); "><i class="ti ti-device-floppy"></i> <span>{{ $editing ? 'Save changes' : 'Add lead source' }}</span></button></div>
</form>
<script>
(() => {
    const form = document.getElementById('lead-source-form');
    const driver = document.getElementById('lead-source-driver');
    const submit = document.getElementById('lead-source-submit');
    if (!form || !driver) return;

    const refreshSourceFields = () => {
        const selected = driver.value;
        document.querySelectorAll('[data-lead-source-driver]').forEach((field) => {
            field.hidden = field.dataset.leadSourceDriver !== selected;
        });
        document.querySelectorAll('[data-lead-source-label]').forEach((label) => {
            label.hidden = label.dataset.leadSourceLabel !== selected;
        });
    };

    driver.addEventListener('change', refreshSourceFields);
    form.addEventListener('submit', () => {
        if (!submit) return;
        submit.disabled = true;
        submit.querySelector('span').textContent = '{{ $editing ? 'Saving...' : 'Adding source...' }}';
    });
    refreshSourceFields();
})();
</script>
@endsection
