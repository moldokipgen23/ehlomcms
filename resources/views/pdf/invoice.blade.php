<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica', sans-serif; color: #2d3748; font-size: 12px; }
    .page { padding: 40px 44px; position: relative; }
    .header { display: flex; justify-content: space-between; padding-bottom: 20px; border-bottom: 2px solid #3b82f6; }
    .brand-name { font-size: 22px; font-weight: bold; color: #1a202c; }
    .brand-tag { font-size: 10px; color: #718096; margin-top: 2px; }
    .doc-title { font-size: 26px; font-weight: bold; color: #3b82f6; text-align: right; }
    .doc-num { font-size: 11px; color: #718096; text-align: right; margin-top: 3px; }
    .meta { width: 100%; margin-top: 22px; }
    .meta td { vertical-align: top; padding-bottom: 4px; }
    .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #a0aec0; font-weight: bold; }
    .bill-name { font-size: 13px; font-weight: bold; color: #1a202c; margin-top: 3px; }
    .bill-line { font-size: 11px; color: #4a5568; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 26px; }
    table.items th { background: #1a202c; color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; padding: 9px 10px; text-align: left; }
    table.items th.r, table.items td.r { text-align: right; }
    table.items td { padding: 9px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
    .totals { width: 240px; float: right; margin-top: 16px; }
    .totals td { padding: 5px 10px; font-size: 11px; }
    .totals td.r { text-align: right; }
    .totals tr.grand td { border-top: 2px solid #1a202c; font-size: 13px; font-weight: bold; color: #1a202c; }
    .notes { clear: both; padding-top: 40px; }
    .notes .body { font-size: 11px; color: #4a5568; margin-top: 4px; }
    .footer { position: absolute; bottom: 36px; left: 44px; right: 44px; text-align: center; font-size: 10px; color: #a0aec0; border-top: 1px solid #e2e8f0; padding-top: 12px; }
    .watermark { position: absolute; top: 300px; left: 0; right: 0; text-align: center; font-size: 90px; font-weight: bold; color: rgba(34,197,94,0.12); transform: rotate(-20deg); }
    .status-pill { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
    .pill-paid { background: #c6f6d5; color: #22543d; }
    .pill-unpaid { background: #feebc8; color: #7b341e; }
    .pill-overdue { background: #fed7d7; color: #742a2a; }
</style>
</head>
<body>
<div class="page">

    @if ($invoice->status === 'paid')
        <div class="watermark">PAID</div>
    @endif

    @php $logo = \App\Models\Setting::imageData('company_logo'); @endphp
    <div class="header">
        <div>
            @if ($logo)
                <img src="{{ $logo }}" alt="Ehlom Digital" style="max-height:54px;max-width:220px;">
            @else
                <div class="brand-name">Ehlom Digital</div>
                <div class="brand-tag">Web Design · Hosting · Digital Solutions</div>
            @endif
        </div>
        <div>
            <div class="doc-title">INVOICE</div>
            <div class="doc-num">{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <table class="meta">
        <tr>
            <td style="width:55%;">
                <div class="label">Billed To</div>
                <div class="bill-name">{{ $invoice->client->name ?? '—' }}</div>
                @if ($invoice->client?->business_name)
                    <div class="bill-line">{{ $invoice->client->business_name }}</div>
                @endif
                @if ($invoice->client?->address)
                    <div class="bill-line">{{ $invoice->client->address }}</div>
                @endif
                @if ($invoice->client?->phone)
                    <div class="bill-line">{{ $invoice->client->phone }}</div>
                @endif
                @if ($invoice->client?->email)
                    <div class="bill-line">{{ $invoice->client->email }}</div>
                @endif
            </td>
            <td class="r" style="text-align:right;">
                <div class="label">Invoice Date</div>
                <div class="bill-line">{{ $invoice->created_at->format('F j, Y') }}</div>
                <div class="label" style="margin-top:8px;">Due Date</div>
                <div class="bill-line">{{ $invoice->due_date?->format('F j, Y') ?? '—' }}</div>
                <div class="label" style="margin-top:8px;">Status</div>
                <div><span class="status-pill pill-{{ $invoice->status }}">{{ $invoice->status }}</span></div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:52%;">Description</th>
                <th class="r">Qty</th>
                <th class="r">Unit Price</th>
                <th class="r">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="r">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td class="r">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="r">{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="r">Rs. {{ number_format($invoice->subtotal, 2) }}</td></tr>
        @if ($invoice->tax_amount > 0)
            <tr><td>GST {{ rtrim(rtrim(number_format($invoice->tax_rate, 2), '0'), '.') }}%</td><td class="r">Rs. {{ number_format($invoice->tax_amount, 2) }}</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="r">Rs. {{ number_format($invoice->total, 2) }}</td></tr>
    </table>

    @if ($invoice->notes)
        <div class="notes">
            <div class="label">Notes</div>
            <div class="body">{{ $invoice->notes }}</div>
        </div>
    @endif

    @php $signature = \App\Models\Setting::imageData('company_signature'); @endphp
    @if ($signature)
        <div style="clear:both;float:right;width:240px;text-align:center;padding-top:30px;">
            <img src="{{ $signature }}" alt="Signature" style="max-height:56px;max-width:200px;">
            <div style="border-top:1px solid #1a202c;margin-top:4px;padding-top:5px;font-size:10px;color:#4a5568;">
                Authorised Signatory — Ehlom Digital
            </div>
        </div>
        <div style="clear:both;"></div>
    @endif

    <div class="footer">Thank you for your business — Ehlom Digital</div>
</div>
</body>
</html>
