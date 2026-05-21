@extends('layouts.app')

@section('title', 'Agreements')
@section('subtitle', $agreements->total() . ' agreements')

@section('topbar-action')
    <a href="{{ route('agreements.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Agreement</a>
@endsection

@section('content')
    <div class="eos-filters">
        <a href="{{ route('agreements.index') }}" class="eos-btn {{ $status ? 'eos-btn-secondary' : 'eos-btn-primary' }}">All</a>
        @foreach (['draft' => 'Draft', 'sent' => 'Sent', 'signed' => 'Signed'] as $key => $label)
            <a href="{{ route('agreements.index', ['status' => $key]) }}" class="eos-btn {{ $status === $key ? 'eos-btn-primary' : 'eos-btn-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead>
                <tr><th>Title</th><th>Client</th><th>Type</th><th>Amount</th><th>Start Date</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($agreements as $agreement)
                    <tr>
                        <td><a href="{{ route('agreements.show', $agreement) }}">{{ $agreement->title }}</a></td>
                        <td>{{ $agreement->client->name ?? '—' }}</td>
                        <td>{{ ucfirst($agreement->type === 'ecommerce' ? 'E-commerce' : $agreement->type) }}</td>
                        <td>₹{{ number_format($agreement->amount, 2) }}</td>
                        <td>{{ $agreement->start_date?->format('M j, Y') ?: '—' }}</td>
                        <td><span class="eos-badge badge-{{ $agreement->status }}">{{ strtoupper($agreement->status) }}</span></td>
                        <td>
                            <div class="eos-actions" style="justify-content:flex-end;">
                                <a href="{{ route('agreements.show', $agreement) }}" class="eos-icon-action edit" title="View"><i class="ti ti-eye"></i></a>
                                <a href="{{ route('agreements.pdf', $agreement) }}" class="eos-icon-action edit" title="Download PDF"><i class="ti ti-download"></i></a>
                                <a href="{{ route('agreements.edit', $agreement) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                <form method="POST" action="{{ route('agreements.destroy', $agreement) }}" onsubmit="return confirm('Delete this agreement?');">
                                    @csrf @method('DELETE')
                                    <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="eos-empty">No agreements found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $agreements->links() }}</div>
@endsection
