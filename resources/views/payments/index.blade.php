@extends('layouts.app')

@section('title', 'Payments')
@section('subtitle', 'All received payments')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div>
        <span style="font-size:16px;font-weight:700;color:var(--text-primary);">{{ $payments->count() }} Payment{{ $payments->count() !== 1 ? 's' : '' }}</span>
        <span style="margin-left:12px;font-size:14px;color:var(--accent-teal);font-weight:700;">₹{{ number_format($total, 2) }} total</span>
    </div>
    <a href="{{ route('payments.create') }}" class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Record Payment</a>
</div>

<table class="eos-table">
    <thead>
        <tr><th>Date</th><th>Invoice</th><th>Client</th><th>Amount</th><th>Method</th><th>Reference</th><th></th></tr>
    </thead>
    <tbody>
        @forelse ($payments as $p)
            <tr>
                <td style="font-size:12px;">{{ $p->payment_date->format('M j, Y') }}</td>
                <td>{{ $p->invoice?->invoice_number ?? '—' }}</td>
                <td>{{ $p->invoice?->client?->name ?? '—' }}</td>
                <td style="font-weight:700;color:var(--accent-green);">₹{{ number_format($p->amount, 2) }}</td>
                <td><span class="eos-badge badge-draft">{{ str_replace('_', ' ', $p->method) }}</span></td>
                <td style="font-size:11px;color:var(--text-muted);">{{ $p->reference ?? '—' }}</td>
                <td style="text-align:right;">
                    <form method="POST" action="{{ route('payments.destroy', $p) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="eos-btn" style="font-size:10px;padding:3px 8px;border:1px solid #ef4444;border-radius:4px;color:#ef4444;background:none;cursor:pointer;" onclick="return confirm('Delete this payment?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="eos-empty">No payments recorded yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
