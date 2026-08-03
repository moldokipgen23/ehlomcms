@extends('layouts.app')

@section('title', isset($prototype) ? 'Edit Prototype' : 'Add Prototype')
@section('subtitle', 'Connect an approved demo to AI lead matching')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;max-width:780px;">
        <div class="eos-card-header"><div class="eos-card-title">{{ isset($prototype) ? 'Update prototype' : 'New prototype' }}</div></div>
        <div style="padding:20px;">
            <form method="POST" action="{{ isset($prototype) ? route('prototype-catalog.update', $prototype) : route('prototype-catalog.store') }}">
                @csrf @if(isset($prototype)) @method('PUT') @endif
                <div class="eos-row" style="gap:14px;">
                    <div class="eos-field" style="flex:1;"><label class="eos-label">Internal key</label><input class="eos-input" name="key" value="{{ old('key', $prototype->key ?? '') }}" placeholder="restaurant" required><div class="eos-hint">Stable key used by the workflow.</div>@error('key')<div class="eos-error">{{ $message }}</div>@enderror</div>
                    <div class="eos-field" style="flex:1;"><label class="eos-label">Display name</label><input class="eos-input" name="name" value="{{ old('name', $prototype->name ?? '') }}" placeholder="Restaurant Website Demo" required>@error('name')<div class="eos-error">{{ $message }}</div>@enderror</div>
                </div>
                <div class="eos-row" style="gap:14px;">
                    <div class="eos-field" style="flex:1;"><label class="eos-label">Business type</label><select class="eos-input" name="business_type" required>@foreach($businessTypes as $key => $type)<option value="{{ $key }}" @selected(old('business_type', $prototype->business_type ?? '') === $key)>{{ $type['name'] ?? ucfirst($key) }}</option>@endforeach</select>@error('business_type')<div class="eos-error">{{ $message }}</div>@enderror</div>
                    <div class="eos-field" style="flex:1;"><label class="eos-label">Assigned theme</label><select class="eos-input" name="theme_key"><option value="">No theme reference</option>@foreach($themes as $theme)<option value="{{ $theme->key }}" @selected(old('theme_key', $prototype->theme_key ?? '') === $theme->key)>{{ $theme->name }} ({{ $theme->key }})</option>@endforeach</select>@error('theme_key')<div class="eos-error">{{ $message }}</div>@enderror</div>
                </div>
                <div class="eos-field" style="margin-bottom:14px;"><label class="eos-label">Published demo URL</label><input class="eos-input" type="url" name="preview_url" value="{{ old('preview_url', $prototype->preview_url ?? '') }}" placeholder="https://restaurantdemo.ehlom.com/"><div class="eos-hint">This link is included in the lead view and outreach draft.</div>@error('preview_url')<div class="eos-error">{{ $message }}</div>@enderror</div>
                <div class="eos-field" style="margin-bottom:14px;"><label class="eos-label">Recommended offer</label><input class="eos-input" name="recommended_offer" value="{{ old('recommended_offer', $prototype->recommended_offer ?? '') }}" placeholder="Restaurant Website Module"></div>
                <div class="eos-field" style="margin-bottom:14px;"><label class="eos-label">Matching keywords</label><textarea class="eos-input" name="keywords" rows="5" placeholder="restaurant, cafe, bakery, dining">{{ old('keywords', implode("\n", $prototype->keywords ?? [])) }}</textarea><div class="eos-hint">One per line or comma separated. Use phrases a Google lead may contain.</div>@error('keywords')<div class="eos-error">{{ $message }}</div>@enderror</div>
                <div class="eos-field" style="margin-bottom:18px;"><label class="eos-label">Status</label><select class="eos-input" name="status"><option value="active" @selected(old('status', $prototype->status ?? 'active') === 'active')>Active — AI may match it</option><option value="paused" @selected(old('status', $prototype->status ?? 'active') === 'paused')>Paused — keep it hidden</option></select></div>
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-device-floppy"></i> {{ isset($prototype) ? 'Save changes' : 'Add prototype' }}</button>
                <a href="{{ route('prototype-catalog.index') }}" class="eos-btn" style="text-decoration:none;border:1px solid var(--border);color:var(--text-secondary);">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
