@extends('layouts.app')

@section('title', 'Add Expense')
@section('subtitle', 'Record a new agency expense')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;max-width:500px;">
        <div class="eos-card-header"><div class="eos-card-title">New Expense</div></div>
        <div style="padding:16px;">
            <form method="POST" action="{{ route('expenses.store') }}">
                @csrf

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Category</label>
                    <select name="category" required class="eos-input">
                        @foreach ($categories as $c)
                            <option value="{{ $c }}" {{ old('category') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required class="eos-input">
                    @error('amount')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Date</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}" required class="eos-input">
                    @error('expense_date')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Vendor</label>
                    <input type="text" name="vendor" value="{{ old('vendor') }}" class="eos-input">
                    @error('vendor')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Description</label>
                    <textarea name="description" rows="3" class="eos-input" style="resize:vertical;">{{ old('description') }}</textarea>
                    @error('description')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="eos-btn eos-btn-primary">Save Expense</button>
                <a href="{{ route('expenses.index') }}" class="eos-btn" style="border:1px solid var(--border);border-radius:8px;text-decoration:none;color:var(--text-secondary);">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
