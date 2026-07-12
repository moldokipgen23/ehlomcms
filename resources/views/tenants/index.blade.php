@extends('layouts.app')

@section('title', 'Tenants')

@section('subtitle', 'All client tenant sites')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        {{ $tenants->count() }} Tenant{{ $tenants->count() !== 1 ? 's' : '' }}
    </div>
    <a href="{{ route('tenants.create') }}" class="eos-btn eos-btn-primary">
        <i class="ti ti-plus"></i> New Tenant
    </a>
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Subdomain</th>
            <th>Name</th>
            <th>Site Type</th>
            <th>Template</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Client</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tenants as $tenant)
            <tr class="{{ $tenant->status === 'suspended' ? 'eos-tr-warn' : '' }}">
                <td>
                    <a href="{{ request()->getScheme() }}://{{ $tenant->subdomain }}.{{ config('app.tenant_domain', 'ehlom.com') }}/" target="_blank" rel="noopener" style="font-size:11px;">
                        {{ $tenant->subdomain }}
                    </a>
                </td>
                <td style="font-weight:600;">{{ $tenant->name }}</td>
                <td><span class="eos-badge {{ $tenant->site_type === 'shopping' ? 'badge-paid' : 'badge-draft' }}">{{ $tenant->site_type ?? '—' }}</span></td>
                <td>{{ $tenant->template_id ?? '—' }}</td>
                <td>{{ $tenant->plan ?? '—' }}</td>
                <td>
                    <span class="eos-badge {{ $tenant->status === 'active' ? 'badge-active' : ($tenant->status === 'suspended' ? 'badge-suspended' : 'badge-pending') }}">
                        {{ $tenant->status }}
                    </span>
                </td>
                <td>
                    @if ($tenant->client)
                        <a href="{{ route('clients.show', $tenant->client) }}" style="color:var(--accent-blue);text-decoration:none;">{{ $tenant->client->name }}</a>
                    @else
                        <span style="color:var(--text-dim);">—</span>
                    @endif
                </td>
                <td>
                    <form method="POST" action="{{ route('tenants.toggle-status', $tenant) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="eos-btn eos-btn-{{ $tenant->status === 'active' ? 'danger' : 'primary' }}" style="font-size:10px;padding:4px 10px;">
                            {{ $tenant->status === 'active' ? 'Suspend' : 'Activate' }}
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8"><div class="eos-empty">No tenants yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection