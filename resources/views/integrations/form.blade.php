@extends('layouts.app')
@php($editing = (bool) $integration)
@section('title', $editing ? 'Edit ERP Integration' : 'Add ERP Integration')
@section('subtitle', 'Use a machine-to-machine API contract for each external product')
@section('content')
<form method="POST" action="{{ $editing ? route('integrations.update', $integration) : route('integrations.store') }}" class="eos-card" style="padding:20px;max-width:860px;">
    @csrf @if($editing) @method('PUT') @endif
    <div class="eos-form-grid">
        <div class="eos-field"><label class="eos-label">Integration name</label><input class="eos-input" name="name" value="{{ old('name', $integration?->name) }}" placeholder="Eiho School ERP"></div>
        <div class="eos-field"><label class="eos-label">Driver</label><select class="eos-input" name="driver"><option value="eschool" @selected(old('driver', $integration?->driver) === 'eschool')>Eschool ERP</option><option value="generic_api" @selected(old('driver', $integration?->driver) === 'generic_api')>Generic Ehlom API</option></select></div>
        <div class="eos-field" style="grid-column:1/-1"><label class="eos-label">Base URL</label><input class="eos-input" type="url" name="base_url" value="{{ old('base_url', $integration?->base_url) }}" placeholder="https://eschool.ehlom.com"></div>
        <div class="eos-field"><label class="eos-label">API key</label><input class="eos-input" name="api_key" type="password" placeholder="Leave blank to keep existing"></div>
        <div class="eos-field"><label class="eos-label">Bearer token</label><input class="eos-input" name="bearer_token" type="password" placeholder="Optional"></div>
        <div class="eos-field"><label class="eos-label">Webhook secret</label><input class="eos-input" name="webhook_secret" type="password" placeholder="Recommended"></div>
        <div class="eos-field"><label class="eos-label">Status</label><select class="eos-input" name="status"><option value="active" @selected(old('status', $integration?->status ?? 'active') === 'active')>Active</option><option value="paused" @selected(old('status', $integration?->status) === 'paused')>Paused</option></select></div>
    </div>
    <div style="margin-top:20px;border-top:1px solid var(--border);padding-top:16px;"><div class="eos-card-title" style="margin-bottom:6px;">API paths</div><div class="eos-row-type">Use the standard defaults or provide paths exposed by the connected ERP.</div></div>
    <div class="eos-form-grid" style="margin-top:12px;">
        @foreach(['catalog' => 'Plans/catalog path', 'accounts' => 'Accounts path', 'subscriptions' => 'Subscriptions path', 'invoices' => 'Invoices path'] as $key => $label)
            <div class="eos-field"><label class="eos-label">{{ $label }}</label><input class="eos-input" name="{{ $key }}_path" value="{{ old($key.'_path', data_get($integration?->settings, 'paths.'.$key)) }}" placeholder="api/v1/integrations/{{ $key }}"></div>
        @endforeach
    </div>
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px;"><a class="eos-btn" href="{{ route('integrations.index') }}">Cancel</a><button class="eos-btn eos-btn-primary"><i class="ti ti-device-floppy"></i> {{ $editing ? 'Save changes' : 'Add integration' }}</button></div>
</form>
@endsection
