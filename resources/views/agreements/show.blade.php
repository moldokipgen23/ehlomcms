@extends('layouts.app')

@section('title', $agreement->title)
@section('subtitle', $agreement->client->name ?? '')

@section('topbar-action')
    <a href="{{ route('agreements.pdf', $agreement) }}" class="eos-icon-btn"><i class="ti ti-download"></i> PDF</a>
    <a href="{{ route('agreements.edit', $agreement) }}" class="eos-icon-btn"><i class="ti ti-pencil"></i> Edit</a>
@endsection

@section('content')
    <div class="eos-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px;">
            <div>
                <div style="font-size:18px;font-weight:700;">{{ $agreement->title }}</div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:3px;">
                    {{ ucfirst($agreement->type === 'ecommerce' ? 'E-commerce' : $agreement->type) }} ·
                    Starts {{ $agreement->start_date?->format('M j, Y') }}
                    @if ($agreement->end_date) · Ends {{ $agreement->end_date->format('M j, Y') }} @endif
                </div>
            </div>
            <span class="eos-badge badge-{{ $agreement->status }}">{{ strtoupper($agreement->status) }}</span>
        </div>

        <div class="eos-form-grid">
            <div>
                <div class="eos-label">Client</div>
                <div style="font-size:13px;color:var(--text-secondary);font-weight:600;">{{ $agreement->client->name ?? '—' }}</div>
                <div style="font-size:11px;color:var(--text-dim);">{{ $agreement->client->business_name }}</div>
            </div>
            <div>
                <div class="eos-label">Amount</div>
                <div class="eos-amt" style="font-size:15px;">₹{{ number_format($agreement->amount, 2) }}</div>
            </div>
        </div>

        @foreach ([
            'Scope of Work' => $agreement->scope,
            'Payment Terms' => $agreement->payment_terms,
            'Terms & Conditions' => $agreement->terms_and_conditions,
            'Notes' => $agreement->notes,
        ] as $label => $body)
            @if ($body)
                <div style="margin-top:14px;">
                    <div class="eos-label">{{ $label }}</div>
                    <div style="font-size:12px;color:var(--text-secondary);white-space:pre-wrap;">{{ $body }}</div>
                </div>
            @endif
        @endforeach
    </div>
@endsection
