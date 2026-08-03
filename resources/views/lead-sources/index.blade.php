@extends('layouts.app')

@section('title', 'Lead Sources')
@section('subtitle', 'Import qualified business prospects from approved sources')

@section('content')
<div class="eos-card" style="margin-bottom:14px;padding:16px 18px;border-left:3px solid var(--accent-blue);">
    <strong>Separate from ERP integrations</strong>
    <div class="eos-row-type" style="margin-top:5px;max-width:820px;">Lead sources find prospects only. Hola imports directory businesses, while Google Places searches by business type and location. Both feed the same deduplicated Leads pipeline and never create subscriptions or invoices.</div>
</div>
<div style="display:flex;justify-content:flex-end;margin-bottom:12px;"><a href="{{ route('lead-sources.create') }}" class="eos-btn eos-btn-primary"><i class="ti ti-database-import"></i> Add lead source</a></div>
<div class="eos-card">
    <div class="eos-card-header"><div class="eos-card-title">Connected lead sources</div><span class="eos-card-link">{{ $sources->count() }} sources</span></div>
    @forelse ($sources as $source)
        <div class="eos-list-item" style="align-items:flex-start;gap:14px;">
            <div style="flex:1;min-width:0;">
                <div class="eos-row-name">{{ $source->name }} <span class="eos-badge badge-{{ $source->status === 'active' ? 'paid' : 'draft' }}">{{ strtoupper($source->status) }}</span></div>
                <div class="eos-row-type">{{ $source->driver }} @if($source->base_url) · {{ $source->base_url }} @endif</div>
                <div class="eos-row-type" style="margin-top:8px;">{{ $source->leads_count }} leads · Last sync: {{ $source->last_synced_at?->diffForHumans() ?: 'Never' }} @if($source->last_imported_count) · {{ $source->last_imported_count }} imported last run @endif</div>
                @if ($source->last_sync_status === 'failed')<div class="eos-error" style="margin-top:8px;">{{ $source->last_error }}</div>@endif
            </div>
            <div style="display:flex;gap:7px;flex-wrap:wrap;justify-content:flex-end;">
                @if ($source->status === 'active')
                    <form method="POST" action="{{ route('lead-sources.sync', $source) }}">@csrf<button class="eos-icon-btn" title="Sync now"><i class="ti ti-refresh"></i></button></form>
                @else
                    <button class="eos-icon-btn" type="button" title="Source paused" disabled style="opacity:.45;cursor:not-allowed;"><i class="ti ti-player-pause"></i></button>
                @endif
                <a class="eos-icon-btn" href="{{ route('lead-sources.edit', $source) }}" title="Edit"><i class="ti ti-pencil"></i></a>
                <form method="POST" action="{{ route('lead-sources.destroy', $source) }}" onsubmit="return confirm('Remove this source? Imported leads will be kept.')">@csrf @method('DELETE')<button class="eos-icon-btn" title="Remove"><i class="ti ti-trash"></i></button></form>
            </div>
        </div>
    @empty
        <div class="eos-empty" style="padding:28px 16px;">No lead sources connected yet.</div>
    @endforelse
</div>
@endsection
