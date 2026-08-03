@extends('layouts.app')

@section('title', 'Prototype Catalog')
@section('subtitle', 'Reusable demos matched to qualified leads')

@section('content')
<div class="eos-card" style="margin-bottom:18px;background:linear-gradient(135deg,rgba(37,99,235,.12),rgba(20,184,166,.08));">
    <div style="padding:20px;display:flex;gap:16px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;">
        <div>
            <div style="font-size:12px;text-transform:uppercase;letter-spacing:.12em;color:#2563eb;font-weight:800;">AI lead matching</div>
            <h2 style="margin:5px 0 6px;font-size:21px;color:var(--text-primary);">One approved demo per business type</h2>
            <p style="margin:0;color:var(--text-secondary);max-width:720px;line-height:1.55;">The AI uses the keywords below to choose a relevant demo for a lead. Edit the catalog here when a new website is published; no workflow code changes are needed.</p>
        </div>
        <a href="{{ route('prototype-catalog.create') }}" class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Add Prototype</a>
    </div>
</div>

<div class="eos-card" style="overflow:hidden;">
    <div class="eos-card-header"><div class="eos-card-title">{{ $prototypes->count() }} Published Prototype{{ $prototypes->count() !== 1 ? 's' : '' }}</div></div>
    <div style="overflow:auto;">
        <table class="eos-table" style="min-width:860px;">
            <thead><tr><th>Prototype</th><th>Business type</th><th>Theme</th><th>Matching keywords</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($prototypes as $prototype)
                <tr>
                    <td>
                        <div style="font-weight:700;color:var(--text-primary);">{{ $prototype->name }}</div>
                        <code style="font-size:11px;color:var(--text-muted);">{{ $prototype->key }}</code>
                        @if ($prototype->preview_url)<div style="margin-top:5px;"><a href="{{ $prototype->preview_url }}" target="_blank" rel="noopener" style="font-size:12px;color:#2563eb;text-decoration:none;"><i class="ti ti-external-link"></i> Open demo</a></div>@endif
                    </td>
                    <td><span class="eos-badge" style="background:rgba(37,99,235,.1);color:#2563eb;">{{ ucfirst($prototype->business_type) }}</span></td>
                    <td><code style="font-size:11px;">{{ $prototype->theme_key ?: 'Not assigned' }}</code></td>
                    <td style="max-width:260px;color:var(--text-secondary);font-size:12px;">{{ implode(', ', $prototype->keywords ?? []) ?: 'No keywords' }}</td>
                    <td><span class="eos-badge" style="background:{{ $prototype->status === 'active' ? 'rgba(16,185,129,.12)' : 'rgba(245,158,11,.14)' }};color:{{ $prototype->status === 'active' ? '#059669' : '#b45309' }};">{{ ucfirst($prototype->status) }}</span></td>
                    <td style="text-align:right;white-space:nowrap;">
                        <a href="{{ route('prototype-catalog.edit', $prototype) }}" class="eos-btn" style="font-size:11px;padding:5px 9px;border:1px solid var(--border);text-decoration:none;color:var(--text-secondary);"><i class="ti ti-pencil"></i> Edit</a>
                        <form method="POST" action="{{ route('prototype-catalog.toggle', $prototype) }}" style="display:inline;">@csrf<button class="eos-btn" type="submit" style="font-size:11px;padding:5px 9px;border:1px solid var(--border);color:var(--text-secondary);background:transparent;cursor:pointer;">{{ $prototype->status === 'active' ? 'Pause' : 'Publish' }}</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="eos-empty">No prototypes yet. Add the first approved demo.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
