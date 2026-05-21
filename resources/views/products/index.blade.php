@extends('layouts.app')

@section('title', 'Products & Services')
@section('subtitle', $products->total() . ' items')

@section('topbar-action')
    <a href="{{ route('products.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Product</a>
@endsection

@section('content')
    <form method="GET" class="eos-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name…" class="eos-input">
        <select name="status" class="eos-select">
            <option value="">All statuses</option>
            @foreach (['active', 'inactive'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="eos-btn eos-btn-secondary">Filter</button>
        @if (request('search') || request('status'))
            <a href="{{ route('products.index') }}" class="eos-btn eos-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead>
                <tr><th>Name</th><th>Type</th><th>Price</th><th>Billing Cycle</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->type }}</td>
                        <td>₹{{ number_format($product->price, 2) }}</td>
                        <td>{{ ucfirst($product->billing_cycle) }}</td>
                        <td><span class="eos-badge badge-{{ $product->status }}">{{ strtoupper($product->status) }}</span></td>
                        <td>
                            <div class="eos-actions" style="justify-content:flex-end;">
                                <a href="{{ route('products.edit', $product) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                    @csrf @method('DELETE')
                                    <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="eos-empty">No products found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $products->links() }}</div>
@endsection
