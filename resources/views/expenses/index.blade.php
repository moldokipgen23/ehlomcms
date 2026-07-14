@extends('layouts.app')

@section('title', 'Expenses')
@section('subtitle', 'Agency expense tracking')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div>
        <span style="font-size:16px;font-weight:700;color:var(--text-primary);">{{ $expenses->count() }} Expense{{ $expenses->count() !== 1 ? 's' : '' }}</span>
        <span style="margin-left:12px;font-size:14px;color:var(--accent-red);font-weight:700;">₹{{ number_format($total, 2) }} total</span>
    </div>
    <a href="{{ route('expenses.create') }}" class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Add Expense</a>
</div>

@if ($byCategory->count())
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        @foreach ($byCategory as $cat => $amt)
            <span class="eos-badge badge-draft">{{ $cat }}: ₹{{ number_format($amt, 0) }}</span>
        @endforeach
    </div>
@endif

<table class="eos-table">
    <thead>
        <tr><th>Date</th><th>Category</th><th>Vendor</th><th>Description</th><th>Amount</th><th></th></tr>
    </thead>
    <tbody>
        @forelse ($expenses as $e)
            <tr>
                <td style="font-size:12px;">{{ $e->expense_date->format('M j, Y') }}</td>
                <td><span class="eos-badge badge-{{ $e->category === 'hosting' ? 'active' : ($e->category === 'salary' ? 'draft' : '') }}">{{ $e->category }}</span></td>
                <td style="font-size:12px;">{{ $e->vendor ?? '—' }}</td>
                <td style="font-size:12px;color:var(--text-secondary);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $e->description ?? '—' }}</td>
                <td style="font-weight:700;color:var(--accent-red);">₹{{ number_format($e->amount, 2) }}</td>
                <td style="text-align:right;">
                    <form method="POST" action="{{ route('expenses.destroy', $e) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="eos-btn" style="font-size:10px;padding:3px 8px;border:1px solid #ef4444;border-radius:4px;color:#ef4444;background:none;cursor:pointer;" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="eos-empty">No expenses recorded.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
