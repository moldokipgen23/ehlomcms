<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica', sans-serif; color: #2d3748; font-size: 12px; }
    .page { padding: 40px 44px; position: relative; }
    .header { display: flex; justify-content: space-between; padding-bottom: 20px; border-bottom: 2px solid #10b981; }
    .brand-name { font-size: 22px; font-weight: bold; color: #1a202c; }
    .brand-tag { font-size: 10px; color: #718096; margin-top: 2px; }
    .doc-title { font-size: 26px; font-weight: bold; color: #10b981; text-align: right; }
    .doc-sub { font-size: 11px; color: #718096; text-align: right; margin-top: 3px; }
    .meta { width: 100%; margin-top: 22px; }
    .meta td { vertical-align: top; padding-bottom: 4px; }
    .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #a0aec0; font-weight: bold; }
    .val { font-size: 11px; color: #2d3748; margin-top: 3px; }
    .val.big { font-size: 13px; font-weight: bold; color: #1a202c; }
    .section { margin-top: 26px; }
    .section h2 { font-size: 13px; color: #1a202c; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
    .prose { font-size: 11.5px; color: #4a5568; line-height: 1.65; white-space: pre-wrap; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
    table.items th { background: #1a202c; color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; padding: 8px 10px; text-align: left; }
    table.items th.r, table.items td.r { text-align: right; }
    table.items td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
    table.subs { width: 100%; border-collapse: collapse; margin-top: 8px; }
    table.subs th { background: #f1f5f9; color: #1a202c; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; padding: 8px 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    table.subs td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
    .pill { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; background:#dcfce7;color:#14532d; }
    .footer { text-align: center; font-size: 10px; color: #a0aec0; border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 36px; }
    .watermark { position: absolute; top: 320px; left: 0; right: 0; text-align: center; font-size: 80px; font-weight: bold; color: rgba(16,185,129,0.10); transform: rotate(-20deg); letter-spacing: 6px; }
</style>
</head>
<body>
<div class="page">

    <div class="watermark">COMPLETED</div>

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
            <div class="doc-title">PROJECT SUMMARY</div>
            <div class="doc-sub">Completion Report</div>
        </div>
    </div>

    <table class="meta">
        <tr>
            <td style="width:55%;">
                <div class="label">Client</div>
                <div class="val big">{{ $project->client->name ?? '—' }}</div>
                @if ($project->client?->business_name)
                    <div class="val">{{ $project->client->business_name }}</div>
                @endif
                @if ($project->client?->email)
                    <div class="val">{{ $project->client->email }}</div>
                @endif
            </td>
            <td style="text-align:right;">
                <div class="label">Project</div>
                <div class="val big">{{ $project->title }}</div>
                <div class="label" style="margin-top:8px;">Status</div>
                <div><span class="pill">Completed</span></div>
                <div class="label" style="margin-top:8px;">Completed On</div>
                <div class="val">{{ ($project->completed_at ?? now())->format('F j, Y') }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding-top:14px;">
                <div class="label">Start Date</div>
                <div class="val">{{ $project->start_date?->format('F j, Y') ?? '—' }}</div>
            </td>
            <td style="padding-top:14px;text-align:right;">
                <div class="label">Delivery Date</div>
                <div class="val">{{ $project->delivery_date?->format('F j, Y') ?? '—' }}</div>
            </td>
        </tr>
    </table>

    @if ($project->description)
        <div class="section">
            <h2>Project Overview</h2>
            <div class="prose">{{ $project->description }}</div>
        </div>
    @endif

    @if ($project->completion_summary)
        <div class="section">
            <h2>Completion Summary</h2>
            <div class="prose">{{ $project->completion_summary }}</div>
        </div>
    @endif

    @if ($project->products->isNotEmpty())
        <div class="section">
            <h2>Delivered Products &amp; Services</h2>
            <table class="items">
                <thead>
                    <tr><th>Item</th><th>Qty</th><th class="r">Unit Price</th><th class="r">Line Total</th></tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach ($project->products as $p)
                        @php
                            $qty = (float) $p->pivot->quantity;
                            $unit = (float) $p->pivot->unit_price;
                            $line = $qty * $unit;
                            $total += $line;
                        @endphp
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</td>
                            <td class="r">₹{{ number_format($unit, 2) }}</td>
                            <td class="r">₹{{ number_format($line, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" class="r" style="font-weight:bold;border-top:2px solid #1a202c;">Project Total</td>
                        <td class="r" style="font-weight:bold;border-top:2px solid #1a202c;">₹{{ number_format($total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if ($project->subscriptions->isNotEmpty())
        <div class="section">
            <h2>Ongoing Services &amp; Subscriptions</h2>
            <table class="subs">
                <thead>
                    <tr><th>Service</th><th>Renews</th><th class="r">Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($project->subscriptions as $s)
                        <tr>
                            <td>{{ $s->product->name ?? '—' }}</td>
                            <td>{{ $s->expiry_date?->format('M j, Y') ?? '—' }}</td>
                            <td class="r">₹{{ number_format((float) $s->renewal_amount, 2) }}</td>
                            <td>{{ ucfirst($s->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($project->invoice)
        <div class="section">
            <h2>Invoice</h2>
            <div class="prose">
Invoice {{ $project->invoice->invoice_number }} — {{ strtoupper($project->invoice->status) }} — ₹{{ number_format((float) $project->invoice->total, 2) }}
            </div>
        </div>
    @endif

    @if ($project->upsell_notes)
        <div class="section">
            <h2>Recommended Next Steps</h2>
            <div class="prose">{{ $project->upsell_notes }}</div>
        </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('F j, Y') }} · Ehlom Digital · This is a project completion record. No signature required.
    </div>

</div>
</body>
</html>
