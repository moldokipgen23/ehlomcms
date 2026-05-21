@extends('layouts.app')

@section('title', 'Invoices')
@section('subtitle', $invoices->total() . ' invoices')

@section('topbar-action')
    <a href="{{ route('invoices.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Invoice</a>
@endsection

@section('content')
    <form method="GET" class="eos-filters">
        <select name="status" class="eos-select">
            <option value="">All statuses</option>
            @foreach (['draft', 'unpaid', 'paid', 'overdue'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="eos-btn eos-btn-secondary">Filter</button>
        @if (request('status'))
            <a href="{{ route('invoices.index') }}" class="eos-btn eos-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead>
                <tr><th>Invoice #</th><th>Client</th><th>Total</th><th>Due Date</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                        <td>{{ $invoice->client->name ?? '—' }}</td>
                        <td>₹{{ number_format($invoice->total, 2) }}</td>
                        <td>{{ $invoice->due_date?->format('M j, Y') ?: '—' }}</td>
                        <td><span class="eos-badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></td>
                        <td>
                            <div class="eos-actions" style="justify-content:flex-end;">
                                <a href="{{ route('invoices.show', $invoice) }}" class="eos-icon-action edit" title="View"><i class="ti ti-eye"></i></a>
                                @if ($invoice->status !== 'draft')
                                    <a href="{{ route('invoices.pdf', $invoice) }}" class="eos-icon-action edit" title="Download PDF"><i class="ti ti-download"></i></a>
                                @endif
                                @if ($invoice->status !== 'paid')
                                    <form method="POST" action="{{ route('invoices.markPaid', $invoice) }}">
                                        @csrf @method('PATCH')
                                        <button class="eos-icon-action" style="color:var(--accent-teal);" title="Mark Paid"><i class="ti ti-check"></i></button>
                                    </form>
                                @endif
                                <a href="{{ route('invoices.edit', $invoice) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice?');">
                                    @csrf @method('DELETE')
                                    <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="eos-empty">No invoices found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $invoices->links() }}</div>
@endsection
