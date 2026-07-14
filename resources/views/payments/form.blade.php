@extends('layouts.app')

@section('title', 'Record Payment')
@section('subtitle', 'Log a received payment')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;max-width:500px;">
        <div class="eos-card-header"><div class="eos-card-title">Record Payment</div></div>
        <div style="padding:16px;">
            <form method="POST" action="{{ route('payments.store') }}">
                @csrf

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Invoice (optional)</label>
                    <select name="invoice_id" class="eos-input">
                        <option value="">— No invoice —</option>
                        @foreach ($invoices as $inv)
                            <option value="{{ $inv->id }}" {{ old('invoice_id') == $inv->id ? 'selected' : '' }}>
                                {{ $inv->invoice_number }} — ₹{{ number_format($inv->total, 0) }} ({{ $inv->client?->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('invoice_id')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required class="eos-input">
                    @error('amount')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required class="eos-input">
                    @error('payment_date')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Method</label>
                    <select name="method" required class="eos-input">
                        @foreach (['bank_transfer','cash','cheque','online','other'] as $m)
                            <option value="{{ $m }}" {{ old('method') === $m ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($m)) }}</option>
                        @endforeach
                    </select>
                    @error('method')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Reference (transaction ID / cheque number)</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" class="eos-input">
                    @error('reference')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Notes</label>
                    <textarea name="notes" rows="3" class="eos-input" style="resize:vertical;">{{ old('notes') }}</textarea>
                    @error('notes')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="eos-btn eos-btn-primary">Record Payment</button>
                <a href="{{ route('payments.index') }}" class="eos-btn" style="border:1px solid var(--border);border-radius:8px;text-decoration:none;color:var(--text-secondary);">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
