@extends('layouts.app')

@section('title', 'Domains & Hosting')
@section('subtitle', $domains->total() . ' domains')

@section('topbar-action')
    <a href="{{ route('domains.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> Add Domain</a>
@endsection

@section('content')
    <div class="eos-filters">
        <a href="{{ route('domains.index') }}" class="eos-btn {{ $filter ? 'eos-btn-secondary' : 'eos-btn-primary' }}">All</a>
        <a href="{{ route('domains.index', ['filter' => 'expiring']) }}" class="eos-btn {{ $filter === 'expiring' ? 'eos-btn-primary' : 'eos-btn-secondary' }}">Expiring Soon</a>
        <a href="{{ route('domains.index', ['filter' => 'expired']) }}" class="eos-btn {{ $filter === 'expired' ? 'eos-btn-primary' : 'eos-btn-secondary' }}">Expired</a>
    </div>

    <div class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead>
                <tr><th>Domain</th><th>Client</th><th>Registrar</th><th>Expiry</th><th>Days Left</th><th>Renewal</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($domains as $domain)
                    @php
                        $days = $domain->days_until_expiry;
                        $rowClass = $days < 0 ? 'eos-tr-danger' : ($days <= 30 ? 'eos-tr-warn' : '');
                        $dayClass = $days <= 7 ? 'days-red' : ($days <= 30 ? 'days-amber' : 'days-green');
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td style="font-weight:600;color:var(--text-primary);">{{ $domain->domain_name }}</td>
                        <td>
                            @if ($domain->client)
                                <a href="{{ route('clients.show', $domain->client) }}">{{ $domain->client->name }}</a>
                            @else — @endif
                        </td>
                        <td>{{ $domain->registrar ?? '—' }}</td>
                        <td>{{ $domain->expiry_date?->format('M j, Y') }}</td>
                        <td><span class="eos-row-days {{ $dayClass }}">{{ $days < 0 ? abs($days) . ' overdue' : $days . ' days' }}</span></td>
                        <td>{{ $domain->renewal_cost !== null ? '₹' . number_format($domain->renewal_cost, 0) : '—' }}</td>
                        <td><span class="eos-badge badge-{{ $domain->status }}">{{ strtoupper($domain->status) }}</span></td>
                        <td>
                            <div class="eos-actions" style="justify-content:flex-end;">
                                <a href="{{ route('domains.edit', $domain) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                <form method="POST" action="{{ route('domains.destroy', $domain) }}" onsubmit="return confirm('Delete this domain?');">
                                    @csrf @method('DELETE')
                                    <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="eos-empty">No domains found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $domains->links() }}</div>
@endsection
