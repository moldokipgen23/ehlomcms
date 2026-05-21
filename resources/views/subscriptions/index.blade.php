@extends('layouts.app')

@section('title', 'Subscriptions')
@section('subtitle', $subscriptions->total() . ' subscriptions')

@section('topbar-action')
    <a href="{{ route('subscriptions.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Subscription</a>
@endsection

@section('content')
    <form method="GET" class="eos-filters">
        <select name="status" class="eos-select">
            <option value="">All statuses</option>
            @foreach (['active', 'expired', 'cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="eos-btn eos-btn-secondary">Filter</button>
        @if (request('status'))
            <a href="{{ route('subscriptions.index') }}" class="eos-btn eos-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead>
                <tr><th>Client</th><th>Product</th><th>Start</th><th>Expiry</th><th>Renewal</th><th>Status</th><th>Days Left</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $sub)
                    @php
                        $days = (int) now()->startOfDay()->diffInDays($sub->expiry_date, false);
                        $rowClass = $days < 0 ? 'eos-tr-danger' : ($days <= 30 ? 'eos-tr-warn' : '');
                        $dayClass = $days < 0 ? 'days-red' : ($days <= 30 ? 'days-amber' : 'days-green');
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>{{ $sub->client->name ?? '—' }}</td>
                        <td>{{ $sub->product->name ?? '—' }}</td>
                        <td>{{ $sub->start_date?->format('M j, Y') }}</td>
                        <td>{{ $sub->expiry_date?->format('M j, Y') }}</td>
                        <td>₹{{ number_format($sub->renewal_amount, 0) }}</td>
                        <td><span class="eos-badge badge-{{ $sub->status }}">{{ strtoupper($sub->status) }}</span></td>
                        <td><span class="eos-row-days {{ $dayClass }}">{{ $days < 0 ? abs($days) . ' overdue' : $days . ' days' }}</span></td>
                        <td>
                            <div class="eos-actions" style="justify-content:flex-end;">
                                @if ($sub->client?->email)
                                    <form method="POST" action="{{ route('subscriptions.sendReminder', $sub) }}"
                                          onsubmit="return confirm('Email a renewal reminder to {{ $sub->client->email }}?');">
                                        @csrf
                                        <button class="eos-icon-action" title="Email renewal reminder"><i class="ti ti-mail"></i></button>
                                    </form>
                                @endif
                                <a href="{{ route('subscriptions.edit', $sub) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                <form method="POST" action="{{ route('subscriptions.destroy', $sub) }}" onsubmit="return confirm('Delete this subscription?');">
                                    @csrf @method('DELETE')
                                    <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="eos-empty">No subscriptions found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $subscriptions->links() }}</div>
@endsection
