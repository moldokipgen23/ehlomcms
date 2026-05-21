@extends('layouts.app')

@section('title', $invoice->invoice_number)
@section('subtitle', $invoice->client->name ?? '')

@section('topbar-action')
    @php
        $cl = $invoice->client;
        $amt = '₹' . number_format($invoice->total, 2);
        $due = $invoice->due_date?->format('M j, Y') ?? 'on receipt';
        $shareMsg = "Hi {$cl?->name}, your invoice {$invoice->invoice_number} from Ehlom Digital is ready. "
            . "Amount: {$amt}. Due: {$due}. Kindly arrange the payment at your convenience. Thank you!";
        $waLink = $cl?->whatsapp ? \App\Helpers\WhatsAppHelper::link($cl->whatsapp, $shareMsg) : null;
    @endphp
    @if ($invoice->status !== 'draft')
        <a href="{{ route('invoices.pdf', $invoice) }}" class="eos-icon-btn"><i class="ti ti-download"></i> PDF</a>
        @if ($waLink)
            <a href="{{ $waLink }}" target="_blank" rel="noopener" class="eos-icon-btn"><i class="ti ti-brand-whatsapp"></i> WhatsApp</a>
        @endif
        @if ($cl?->email)
            <form method="POST" action="{{ route('invoices.sendEmail', $invoice) }}" style="display:inline;"
                  onsubmit="return confirm('Email invoice {{ $invoice->invoice_number }} (with PDF attached) to {{ $cl->email }}?');">
                @csrf
                <button type="submit" class="eos-icon-btn"><i class="ti ti-mail"></i> Send Email</button>
            </form>
        @endif
    @endif
    <a href="{{ route('invoices.edit', $invoice) }}" class="eos-icon-btn"><i class="ti ti-pencil"></i> Edit</a>
@endsection

@section('content')
    <div class="eos-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px;">
            <div>
                <div style="font-size:18px;font-weight:700;">{{ $invoice->invoice_number }}</div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:3px;">
                    Issued {{ $invoice->created_at->format('M j, Y') }}
                    @if ($invoice->due_date) · Due {{ $invoice->due_date->format('M j, Y') }} @endif
                </div>
            </div>
            <div style="text-align:right;">
                <span class="eos-badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
                @if ($invoice->status !== 'paid')
                    <form method="POST" action="{{ route('invoices.markPaid', $invoice) }}" style="margin-top:8px;">
                        @csrf @method('PATCH')
                        <button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Mark as Paid</button>
                    </form>
                @endif
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="eos-label">Billed To</div>
            <div style="font-size:13px;color:var(--text-secondary);font-weight:600;">{{ $invoice->client->name ?? '—' }}</div>
            <div style="font-size:11px;color:var(--text-dim);">
                {{ $invoice->client->business_name }}
                @if ($invoice->client?->phone) · {{ $invoice->client->phone }} @endif
            </div>
        </div>

        <table class="eos-table">
            <thead><tr><th style="width:55%;">Description</th><th>Qty</th><th>Unit Price</th><th style="text-align:right;">Total</th></tr></thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td>₹{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align:right;">₹{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="display:flex;justify-content:flex-end;margin-top:14px;">
            <div style="width:260px;">
                <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;color:var(--text-secondary);">
                    <span>Subtotal</span><span class="eos-amt">₹{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if ($invoice->tax_amount > 0)
                    <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;color:var(--text-secondary);">
                        <span>GST ({{ rtrim(rtrim(number_format($invoice->tax_rate, 2), '0'), '.') }}%)</span><span class="eos-amt">₹{{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                @endif
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--border);font-weight:700;">
                    <span>Total</span><span class="eos-amt" style="font-size:14px;">₹{{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
        </div>

        @if ($invoice->notes)
            <div style="margin-top:14px;">
                <div class="eos-label">Notes</div>
                <div style="font-size:12px;color:var(--text-secondary);white-space:pre-wrap;">{{ $invoice->notes }}</div>
            </div>
        @endif
    </div>
@endsection
