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

<table class="eos-table" style="margin-bottom:28px;">
    <thead>
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Visibility</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($addons as $addon)
            <tr>
                <td style="font-weight:600;">
                    <i class="ti {{ $addon->icon }}" style="margin-right:6px;color:var(--text-muted);"></i>
                    {{ $addon->name }}
                    @if ($addon->description)
                        <div style="font-size:11px;color:var(--text-dim);font-weight:400;margin-top:2px;">{{ $addon->description }}</div>
                    @endif
                </td>
                <td>₹{{ number_format($addon->price, 0) }}/mo</td>
                <td>
                    <form action="{{ route('addon-products.toggle-active', $addon) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="eos-badge {{ $addon->active ? 'badge-active' : 'badge-pending' }}" style="border:none;cursor:pointer;">
                            {{ $addon->active ? 'Visible to clients' : 'Hidden' }}
                        </button>
                    </form>
                </td>
                <td style="display:flex;gap:6px;">
                    <a href="{{ route('addon-products.edit', $addon) }}" class="eos-btn eos-btn-secondary" style="font-size:10px;padding:4px 10px;">
                        <i class="ti ti-edit"></i> Edit
                    </a>
                    <form action="{{ route('addon-products.destroy', $addon) }}" method="POST" onsubmit="return confirm('Delete this add-on?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="eos-btn eos-btn-danger" style="font-size:10px;padding:4px 10px;">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4"><div class="eos-empty">No products or services yet.</div></td></tr>
        @endforelse
    </tbody>
</table>

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
