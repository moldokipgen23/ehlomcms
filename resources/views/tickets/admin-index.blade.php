@extends('layouts.app')

@section('title', 'Support Tickets')
@section('subtitle', 'All tenant support requests')

@section('content')
<table class="eos-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tenant</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tickets as $ticket)
            <tr>
                <td style="font-weight:600;">#{{ $ticket->id }}</td>
                <td>{{ $ticket->tenant->name ?? '—' }}</td>
                <td>{{ $ticket->subject }}</td>
                <td>
                    <span class="eos-badge badge-{{ $ticket->status === 'closed' ? 'suspended' : ($ticket->status === 'replied' ? 'active' : 'draft') }}">
                        {{ $ticket->status }}
                    </span>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $ticket->created_at->format('M j, Y g:i A') }}</td>
                <td style="text-align:right;">
                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;text-decoration:none;color:var(--text-secondary);">View</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="eos-empty">No tickets yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
