@extends('layouts.app')

@section('title', 'Products & Services')
@section('subtitle', $products->total() . ' custom ' . \Illuminate\Support\Str::plural('product', $products->total()))

@section('topbar-action')
    <a href="{{ route('products.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Product</a>
@endsection

@section('content')
    <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:12px;">
        Your own custom add-on products &amp; services — Web Design, Mobile App, and the like.
        Hosting plans and domain extension prices live under <a href="{{ route('infrastructure.index') }}">Hosting &amp; Domains</a>.
    </p>

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
                <tr><th>Name</th><th>Price</th><th>Billing</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td style="font-weight:600;color:var(--text-primary);">{{ $product->name }}</td>
                        <td>₹{{ number_format($product->price, 2) }}</td>
                        <td>{{ \App\Models\Product::BILLING_LABELS[$product->billing_cycle] ?? ucfirst($product->billing_cycle) }}</td>
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
                    <tr><td colspan="5"><div class="eos-empty">No custom products yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $products->links() }}</div>
@endsection
