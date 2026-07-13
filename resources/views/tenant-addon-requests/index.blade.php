@extends('layouts.app')

@section('title', 'Add-on Requests')

@section('subtitle', 'Every tenant add-on request across the platform')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        {{ $requests->count() }} Request{{ $requests->count() !== 1 ? 's' : '' }}
    </div>
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Tenant</th>
            <th>Add-on</th>
            <th>Price</th>
            <th>Status</th>
            <th>Requested / Activated</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($requests as $req)
            @php $addon = $addons[$req->addon_key] ?? null; @endphp
            <tr class="{{ $req->status === 'pending' ? 'eos-tr-warn' : '' }}">
                <td style="font-weight:600;">
                    {{ $req->tenant->name ?? '—' }}
                    @if ($req->tenant?->client)
                        <div style="font-size:11px;color:var(--text-dim);font-weight:400;">
                            Linked to client: {{ $req->tenant->client->name }}
                        </div>
                    @endif
                </td>
                <td>{{ $addon['name'] ?? $req->addon_key }}</td>
                <td>₹{{ number_format($addon['price'] ?? 0, 0) }}/mo</td>
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
            <tr><td colspan="6"><div class="eos-empty">No add-on requests yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
