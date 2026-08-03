@extends('layouts.app')

@section('title', 'ERP Integrations')
@section('subtitle', 'Connect external products without mixing their operational data into Ehlom')

@section('content')
<div class="eos-card" style="margin-bottom:14px;padding:16px 18px;border-left:3px solid var(--accent-blue);">
    <strong>Central billing control</strong>
    <div class="eos-row-type" style="margin-top:5px;max-width:760px;">External ERPs remain responsible for their own operations. Ehlom mirrors plans, accounts, subscriptions, invoices, and payment events for sales, revenue, and client tracking.</div>
</div>
<div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
    <a href="{{ route('integrations.create') }}" class="eos-btn eos-btn-primary"><i class="ti ti-plug-connected"></i> Add ERP integration</a>
</div>
<div class="eos-card">
    <div class="eos-card-header"><div class="eos-card-title">Connected products</div><span class="eos-card-link">{{ $integrations->count() }} integrations</span></div>
    @forelse ($integrations as $integration)
        <div class="eos-list-item" style="align-items:flex-start;gap:14px;">
            <div style="flex:1;min-width:0;">
                <div class="eos-row-name">{{ $integration->name }} <span class="eos-badge badge-{{ $integration->status === 'active' ? 'paid' : 'draft' }}">{{ strtoupper($integration->status) }}</span></div>
                <div class="eos-row-type">{{ $integration->driver }} · {{ $integration->base_url }}</div>
                <div class="eos-row-type" style="margin-top:8px;">{{ $integration->catalog_products_count }} plans · {{ $integration->accounts_count }} accounts · {{ $integration->subscriptions_count }} subscriptions · {{ $integration->invoices_count }} invoices</div>
                @if ($integration->last_sync_status === 'failed')<div class="eos-error" style="margin-top:8px;">{{ $integration->last_error }}</div>@endif
            </div>
            <div style="display:flex;gap:7px;flex-wrap:wrap;justify-content:flex-end;">
                <form method="POST" action="{{ route('integrations.sync', $integration) }}">@csrf<button class="eos-icon-btn" title="Sync now"><i class="ti ti-refresh"></i></button></form>
                <a class="eos-icon-btn" href="{{ route('integrations.edit', $integration) }}" title="Edit"><i class="ti ti-pencil"></i></a>
                <form method="POST" action="{{ route('integrations.destroy', $integration) }}" onsubmit="return confirm('Remove this integration and its imported records?')">@csrf @method('DELETE')<button class="eos-icon-btn" title="Remove"><i class="ti ti-trash"></i></button></form>
            </div>
        </div>
    @empty
        <div class="eos-empty" style="padding:28px 16px;">No external ERP integrations connected yet.</div>
    @endforelse
</div>
@endsection
