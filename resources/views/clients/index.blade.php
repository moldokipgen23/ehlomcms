@extends('layouts.app')

@section('title', 'Clients')
@section('subtitle', $clients->total() . ' total clients')

@section('topbar-action')
    <a href="{{ route('clients.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Client</a>
@endsection

@section('content')
    <form method="GET" class="eos-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or business…" class="eos-input">
        <select name="status" class="eos-select">
            <option value="">All statuses</option>
            @foreach (['active', 'inactive', 'suspended'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="eos-btn eos-btn-secondary">Filter</button>
        @if (request('search') || request('status'))
            <a href="{{ route('clients.index') }}" class="eos-btn eos-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead>
                <tr>
                    <th>Name</th><th>Business</th><th>Phone</th><th>WhatsApp</th><th>Status</th><th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td><a href="{{ route('clients.show', $client) }}">{{ $client->name }}</a></td>
                        <td>{{ $client->business_name ?: '—' }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>
                            @if ($client->whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $client->whatsapp) }}" target="_blank" style="color:var(--accent-teal);"><i class="ti ti-brand-whatsapp"></i> Chat</a>
                            @else — @endif
                        </td>
                        <td><span class="eos-badge badge-{{ $client->status }}">{{ strtoupper($client->status) }}</span></td>
                        <td>
                            <div class="eos-actions" style="justify-content:flex-end;">
                                <a href="{{ route('clients.edit', $client) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Delete this client? This also removes their projects, subscriptions and invoices.');">
                                    @csrf @method('DELETE')
                                    <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="eos-empty">No clients found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $clients->links() }}</div>
@endsection
