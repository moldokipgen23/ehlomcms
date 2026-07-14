@extends('layouts.app')

@section('title', 'Add-on Marketplace')

@section('subtitle', 'Add-ons your platform tenants can request (WhatsApp Automation, AI Agent, etc.) and who has requested or activated them')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        Your Add-ons
    </div>
    <a href="{{ route('addon-products.create') }}" class="eos-btn eos-btn-primary">
        <i class="ti ti-plus"></i> New Add-on
    </a>
</div>

@if ($addons->isEmpty())
    <div class="eos-empty" style="margin-bottom:28px;">No add-ons yet.</div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;margin-bottom:28px;">
        @foreach ($addons as $addon)
            @php $s = $stats[$addon->key] ?? ['active' => 0, 'pending' => 0]; @endphp
            <div class="eos-card" style="padding:14px;display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                    <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                        <i class="ti {{ $addon->icon }}" style="font-size:18px;color:var(--text-muted);flex:none;"></i>
                        <div style="font-weight:600;color:var(--text-primary);font-size:14px;">{{ $addon->name }}</div>
                    </div>
                    <div style="font-size:12px;color:var(--text-dim);white-space:nowrap;">₹{{ number_format($addon->price, 0) }}/mo</div>
                </div>

                @if ($addon->description)
                    <div style="font-size:12px;color:var(--text-dim);line-height:1.5;">{{ $addon->description }}</div>
                @endif

                <div style="display:flex;flex-wrap:wrap;gap:5px;">
                    @forelse ($addon->business_types ?: [] as $bt)
                        <span class="eos-badge badge-draft" style="font-size:10px;">{{ config("business_types.$bt.label", $bt) }}</span>
                    @empty
                        <span class="eos-badge badge-draft" style="font-size:10px;">All business types</span>
                    @endforelse
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:8px;border-top:1px solid var(--border);">
                    <span class="eos-badge" style="font-size:10px;background:var(--bg-hover);color:var(--text-secondary);">
                        {{ $s['active'] }} active @if($s['pending']) &middot; {{ $s['pending'] }} pending @endif
                    </span>
                    <form action="{{ route('addon-products.toggle-active', $addon) }}" method="POST">
                        @csrf
                        <button type="submit" class="eos-badge {{ $addon->active ? 'badge-active' : 'badge-pending' }}" style="border:none;cursor:pointer;">
                            {{ $addon->active ? 'Visible' : 'Hidden' }}
                        </button>
                    </form>
                </div>

                <div style="display:flex;gap:6px;">
                    <a href="{{ route('addon-products.edit', $addon) }}" class="eos-btn eos-btn-secondary" style="font-size:10px;padding:4px 10px;flex:1;text-align:center;">
                        <i class="ti ti-edit"></i> Edit
                    </a>
                    <form action="{{ route('addon-products.destroy', $addon) }}" method="POST" onsubmit="return confirm('Delete this add-on?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="eos-btn eos-btn-danger" style="font-size:10px;padding:4px 10px;">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        Client Requests & Activations
    </div>
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Client Site</th>
            <th>Product/Service</th>
            <th>Price</th>
            <th>Status</th>
            <th>Requested / Activated</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($requests as $req)
            @php $addon = $addons->firstWhere('key', $req->addon_key); @endphp
            <tr class="{{ $req->status === 'pending' ? 'eos-tr-warn' : '' }}">
                <td style="font-weight:600;">
                    {{ $req->tenant->name ?? '—' }}
                    @if ($req->tenant?->client)
                        <div style="font-size:11px;color:var(--text-dim);font-weight:400;">
                            Linked to client: {{ $req->tenant->client->name }}
                        </div>
                    @endif
                </td>
                <td>{{ $addon->name ?? $req->addon_key }}</td>
                <td>₹{{ number_format($addon->price ?? 0, 0) }}/mo</td>
                <td>
                    <span class="eos-badge {{ $req->status === 'active' ? 'badge-active' : ($req->status === 'pending' ? 'badge-pending' : 'badge-draft') }}">
                        {{ ucfirst($req->status) }}
                    </span>
                </td>
                <td style="font-size:11px;color:var(--text-muted);">
                    {{ $req->activated_at?->format('M j, Y') ?? $req->created_at->format('M j, Y') }}
                </td>
                <td>
                    @if ($req->status === 'pending')
                        <form action="{{ route('addon-requests.activate', $req) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="eos-btn eos-btn-primary" style="font-size:10px;padding:4px 10px;">
                                <i class="ti ti-check"></i> Activate
                            </button>
                        </form>
                    @elseif ($req->status === 'active')
                        <form action="{{ route('addon-requests.deactivate', $req) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="eos-btn eos-btn-danger" style="font-size:10px;padding:4px 10px;">
                                Deactivate
                            </button>
                        </form>
                    @else
                        <span style="color:var(--text-dim);font-size:11px;">—</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="eos-empty">No requests yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
