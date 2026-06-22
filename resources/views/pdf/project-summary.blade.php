<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; color: #1f2937; font-size: 11.5px; line-height: 1.55; }

    /* A .sheet is a logical page group; only the first inside a block sets
       the page break so short sections share a page. */
    .sheet { padding: 44px 48px 80px; position: relative; }
    .sheet + .sheet { page-break-before: always; }
    .section { margin-top: 30px; }
    .section.tight { margin-top: 22px; }

    /* Brand bar on every page */
    .topbar { display: table; width: 100%; padding-bottom: 18px; border-bottom: 2px solid #10b981; margin-bottom: 26px; }
    .topbar .l, .topbar .r { display: table-cell; vertical-align: middle; }
    .topbar .r { text-align: right; }
    .brand-name { font-size: 20px; font-weight: bold; color: #0f172a; }
    .brand-tag { font-size: 9.5px; color: #6b7280; margin-top: 2px; }
    .doc-tag { font-size: 9px; letter-spacing: 2px; color: #10b981; font-weight: bold; text-transform: uppercase; }

    h1.page-title { font-size: 22px; color: #0f172a; font-weight: bold; margin-bottom: 4px; letter-spacing: -.3px; }
    .page-sub { font-size: 11px; color: #6b7280; margin-bottom: 24px; }

    .meta { width: 100%; margin-bottom: 28px; border-collapse: collapse; }
    .meta td { vertical-align: top; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
    .meta .lbl { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #94a3b8; font-weight: bold; }
    .meta .v { font-size: 12px; color: #0f172a; margin-top: 2px; }
    .meta .v.big { font-size: 14px; font-weight: bold; }

    .prose { font-size: 12px; color: #334155; line-height: 1.75; white-space: pre-wrap; }

    /* Tables — keep together on one page */
    table.data { width: 100%; border-collapse: collapse; margin-top: 6px; page-break-inside: avoid; }
    table.data th { background: #0f172a; color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: 1.2px; padding: 10px 12px; text-align: left; font-weight: bold; }
    table.data th.r, table.data td.r { text-align: right; }
    table.data td { padding: 11px 12px; border-bottom: 1px solid #e5e7eb; font-size: 11.5px; color: #1f2937; }
    table.data tbody tr:last-child td { border-bottom: none; }
    table.data tfoot td { font-weight: bold; font-size: 12.5px; background: #f8fafc; border-top: 2px solid #0f172a; padding: 12px; color: #0f172a; }

    .pill { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; background:#dcfce7; color:#166534; }

    .footer { position: absolute; bottom: 40px; left: 48px; right: 48px; text-align: center; font-size: 9.5px; color: #94a3b8; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    .pageno { float: right; }

    .watermark { position: absolute; top: 360px; left: 0; right: 0; text-align: center; font-size: 88px; font-weight: bold; color: rgba(16,185,129,0.07); transform: rotate(-22deg); letter-spacing: 8px; z-index: 0; }

    .stack > div + div { margin-top: 18px; }
</style>
</head>
<body>

@php
    $logo = \App\Models\Setting::imageData('company_logo');
@endphp

@php
    $renderTopbar = function ($logo, $tag) {
        return '<div class="topbar">'
            . '<div class="l">'
            . ($logo ? '<img src="' . $logo . '" alt="Ehlom Digital" style="max-height:42px;max-width:180px;">' : '<div class="brand-name">Ehlom Digital</div>')
            . '</div>'
            . '<div class="r"><div class="doc-tag">' . $tag . '</div></div>'
            . '</div>';
    };
@endphp

{{-- ───── PAGE 1 · COVER + OVERVIEW + COMPLETION SUMMARY ───── --}}
<div class="sheet">
    <div class="watermark">COMPLETED</div>

    {!! $renderTopbar($logo, 'Project Summary') !!}

    <h1 class="page-title">{{ $project->title }}</h1>
    <div class="page-sub">Completion report for {{ $project->client->name ?? '—' }}</div>

    <table class="meta">
        <tr>
            <td style="width:50%;">
                <div class="lbl">Client</div>
                <div class="v big">{{ $project->client->name ?? '—' }}</div>
                @if ($project->client?->business_name)
                    <div class="v" style="color:#475569;">{{ $project->client->business_name }}</div>
                @endif
                @if ($project->client?->email)
                    <div class="v" style="color:#475569;font-size:11px;">{{ $project->client->email }}</div>
                @endif
            </td>
            <td style="width:50%;text-align:right;">
                <div class="lbl">Status</div>
                <div style="margin-top:4px;"><span class="pill">Completed</span></div>
                <div class="lbl" style="margin-top:14px;">Completed On</div>
                <div class="v">{{ ($project->completed_at ?? now())->format('F j, Y') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="lbl">Start Date</div>
                <div class="v">{{ $project->start_date?->format('F j, Y') ?? '—' }}</div>
            </td>
            <td style="text-align:right;">
                <div class="lbl">Delivery Date</div>
                <div class="v">{{ $project->delivery_date?->format('F j, Y') ?? '—' }}</div>
            </td>
        </tr>
    </table>

    @if ($project->description)
        <div class="section tight">
            <div class="lbl" style="margin-bottom:8px;">Project Overview</div>
            <div class="prose">{{ $project->description }}</div>
        </div>
    @endif

    @if ($project->completion_summary)
        <div class="section">
            <div class="lbl" style="margin-bottom:8px;">Completion Summary</div>
            <div class="prose">{{ $project->completion_summary }}</div>
        </div>
    @endif

    <div class="footer">Ehlom Digital · Project Summary <span class="pageno">Page 1</span></div>
</div>

{{-- ───── PAGE 2 · DELIVERABLES + ONGOING SERVICES ───── --}}
@if ($project->products->isNotEmpty() || $project->subscriptions->isNotEmpty())
<div class="sheet">
    {!! $renderTopbar($logo, 'Deliverables &amp; Services') !!}

    @if ($project->products->isNotEmpty())
        <h1 class="page-title">Delivered Products &amp; Services</h1>
        <div class="page-sub">Itemised breakdown of everything included in this project.</div>

        @php $total = 0; @endphp
        <table class="data">
            <thead>
                <tr><th>Item</th><th>Qty</th><th class="r">Unit Price</th><th class="r">Line Total</th></tr>
            </thead>
            <tbody>
                @foreach ($project->products as $p)
                    @php
                        $qty = (float) $p->pivot->quantity;
                        $unit = (float) $p->pivot->unit_price;
                        $line = $qty * $unit;
                        $total += $line;
                    @endphp
                    <tr>
                        <td style="font-weight:600;">{{ $p->name }}</td>
                        <td>{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</td>
                        <td class="r">₹{{ number_format($unit, 2) }}</td>
                        <td class="r">₹{{ number_format($line, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="r">Project Total</td>
                    <td class="r">₹{{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($project->invoice)
            <div style="margin-top:20px;padding:12px 14px;background:#f8fafc;border-left:3px solid #10b981;">
                <div class="lbl">Invoice Reference</div>
                <div style="font-size:12.5px;color:#0f172a;margin-top:5px;">
                    <strong>{{ $project->invoice->invoice_number }}</strong> · {{ strtoupper($project->invoice->status) }} · ₹{{ number_format((float) $project->invoice->total, 2) }}
                </div>
            </div>
        @endif
    @endif

    @if ($project->subscriptions->isNotEmpty())
        <div class="section">
            <h1 class="page-title" style="font-size:18px;">Ongoing Services &amp; Renewals</h1>
            <div class="page-sub">Subscriptions tied to this project and their renewal schedule.</div>

            <table class="data">
                <thead>
                    <tr><th>Service</th><th>Renews On</th><th class="r">Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($project->subscriptions as $s)
                        <tr>
                            <td style="font-weight:600;">{{ $s->product->name ?? '—' }}</td>
                            <td>{{ $s->expiry_date?->format('F j, Y') ?? '—' }}</td>
                            <td class="r">₹{{ number_format((float) $s->renewal_amount, 2) }}</td>
                            <td>{{ ucfirst($s->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:14px;font-size:10.5px;color:#64748b;line-height:1.7;">
                You'll receive a renewal reminder approximately 30 days before each expiry date.
            </div>
        </div>
    @endif

    <div class="footer">Ehlom Digital · Project Summary <span class="pageno">Page 2</span></div>
</div>
@endif

{{-- ───── PAGE 3 · NEXT STEPS + THANK YOU CLOSING ───── --}}
<div class="sheet">
    {!! $renderTopbar($logo, 'Looking Ahead') !!}

    @if ($project->upsell_notes)
        <h1 class="page-title">Recommended Next Steps</h1>
        <div class="page-sub">Suggested future phases and enhancements for {{ $project->title }}.</div>
        <div class="prose">{{ $project->upsell_notes }}</div>
    @endif

    <div style="margin-top:{{ $project->upsell_notes ? '60px' : '120px' }};text-align:center;">
        <div style="font-size:28px;font-weight:bold;color:#0f172a;letter-spacing:-.5px;">Thank You</div>
        <div style="font-size:13px;color:#475569;margin-top:12px;line-height:1.7;max-width:420px;margin-left:auto;margin-right:auto;">
            It has been a pleasure delivering <strong>{{ $project->title }}</strong> for {{ $project->client->name ?? 'you' }}.
            We look forward to supporting your continued growth.
        </div>
        <div style="margin-top:26px;">
            <span style="display:inline-block;padding:9px 22px;background:#0f172a;color:#fff;font-size:10.5px;letter-spacing:1.5px;text-transform:uppercase;">
                Project Officially Closed
            </span>
        </div>
        <div style="margin-top:22px;font-size:10.5px;color:#94a3b8;">
            Completed on {{ ($project->completed_at ?? now())->format('F j, Y') }}
        </div>
    </div>

    <div class="footer">This is a project completion record. No signature required. <span class="pageno">Ehlom Digital</span></div>
</div>

</body>
</html>
