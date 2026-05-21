@extends('layouts.app')

@section('title', $project->title)
@section('subtitle', 'Project detail')

@section('topbar-action')
    <a href="{{ route('projects.edit', $project) }}" class="eos-icon-btn"><i class="ti ti-pencil"></i> Edit</a>
@endsection

@section('content')
    @php
        $productsTotal = $project->products_total;
        $gst = round($productsTotal * 0.18, 2);
    @endphp

    <div class="eos-card" style="margin-bottom:14px;">
        <div class="eos-card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="eos-card-title">{{ $project->title }}</div>
            <span class="eos-badge badge-{{ $project->status }}">{{ strtoupper(str_replace('_', ' ', $project->status)) }}</span>
        </div>
        <div class="eos-form-grid">
            <div>
                <div class="eos-label">Client</div>
                <div style="font-size:12.5px;">
                    <a href="{{ route('clients.show', $project->client) }}">{{ $project->client->name ?? '—' }}</a>
                </div>
            </div>
            <div>
                <div class="eos-label">Start Date</div>
                <div style="font-size:12.5px;color:var(--text-secondary);">{{ $project->start_date?->format('M j, Y') ?: '—' }}</div>
            </div>
            <div>
                <div class="eos-label">Delivery Date</div>
                <div style="font-size:12.5px;color:var(--text-secondary);">{{ $project->delivery_date?->format('M j, Y') ?: '—' }}</div>
            </div>
        </div>
        @if ($project->description)
            <div style="margin-top:10px;">
                <div class="eos-label">Description</div>
                <div style="font-size:12.5px;color:var(--text-secondary);white-space:pre-wrap;">{{ $project->description }}</div>
            </div>
        @endif
        @if ($project->notes)
            <div style="margin-top:10px;">
                <div class="eos-label">Internal Notes</div>
                <div style="font-size:12.5px;color:var(--text-secondary);white-space:pre-wrap;">{{ $project->notes }}</div>
            </div>
        @endif
    </div>

    {{-- Included products --}}
    <div class="eos-card" style="margin-bottom:14px;padding:0;">
        <div class="eos-card-header" style="padding:14px 14px 0;"><div class="eos-card-title">Included Products &amp; Services</div></div>
        <table class="eos-table">
            <thead>
                <tr><th>Product / Service</th><th>Qty</th><th>Unit Price</th><th style="text-align:right;">Line Total</th></tr>
            </thead>
            <tbody>
                @forelse ($project->products as $product)
                    <tr>
                        <td style="font-weight:600;color:var(--text-primary);">{{ $product->name }}</td>
                        <td>{{ rtrim(rtrim(number_format($product->pivot->quantity, 2), '0'), '.') }}</td>
                        <td>₹{{ number_format($product->pivot->unit_price, 2) }}</td>
                        <td style="text-align:right;">₹{{ number_format($product->pivot->quantity * $product->pivot->unit_price, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="eos-empty">No products added to this project yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($project->products->isNotEmpty())
            <div style="padding:12px 14px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;">
                <div style="font-weight:700;font-size:14px;">Project Total: ₹{{ number_format($productsTotal, 2) }}</div>
            </div>
        @endif
    </div>

    {{-- Invoice --}}
    <div class="eos-card">
        <div class="eos-card-header"><div class="eos-card-title">Invoice</div></div>
        @if ($project->invoice)
            <p style="font-size:12px;color:var(--text-secondary);margin-bottom:10px;">
                Invoice <strong>{{ $project->invoice->invoice_number }}</strong> —
                <span class="eos-badge badge-{{ $project->invoice->status }}">{{ strtoupper($project->invoice->status) }}</span>
                · Total ₹{{ number_format($project->invoice->total, 2) }}
            </p>
            <a href="{{ route('invoices.show', $project->invoice) }}" class="eos-btn eos-btn-primary">
                <i class="ti ti-file-invoice"></i> View Invoice
            </a>
        @else
            <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:10px;">
                Creates a draft invoice with every product above as a line item (GST 18% applied). You can review and edit it before sending.
            </p>
            <form method="POST" action="{{ route('projects.generateInvoice', $project) }}">
                @csrf
                <button class="eos-btn eos-btn-primary" {{ $project->products->isEmpty() ? 'disabled' : '' }}>
                    <i class="ti ti-file-plus"></i> Generate Invoice
                </button>
            </form>
        @endif
    </div>
@endsection
