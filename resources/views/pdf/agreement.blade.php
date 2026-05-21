<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica', sans-serif; color: #2d3748; font-size: 12px; line-height: 1.5; }
    .page { padding: 40px 44px 90px; position: relative; }
    .header { display: flex; justify-content: space-between; padding-bottom: 20px; border-bottom: 2px solid #3b82f6; }
    .brand-name { font-size: 22px; font-weight: bold; color: #1a202c; }
    .brand-tag { font-size: 10px; color: #718096; margin-top: 2px; }
    .brand-contact { font-size: 9px; color: #a0aec0; margin-top: 4px; }
    .doc-title { font-size: 22px; font-weight: bold; color: #3b82f6; text-align: right; }
    .doc-type { font-size: 10px; color: #718096; text-align: right; margin-top: 3px; text-transform: uppercase; letter-spacing: 1px; }
    .meta { width: 100%; margin-top: 22px; }
    .meta td { vertical-align: top; padding-bottom: 4px; width: 50%; }
    .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #a0aec0; font-weight: bold; }
    .value { font-size: 12px; color: #1a202c; margin-top: 2px; }
    .section { margin-top: 22px; }
    .section h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #3b82f6; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 8px; }
    .section .body { font-size: 11px; color: #4a5568; white-space: pre-wrap; }
    .pay-amount { font-size: 16px; font-weight: bold; color: #1a202c; }
    .signature { width: 100%; margin-top: 50px; }
    .signature td { width: 50%; vertical-align: bottom; padding-top: 38px; }
    .sign-line { border-top: 1px solid #1a202c; width: 80%; padding-top: 5px; font-size: 10px; color: #4a5568; }
    .footer { position: absolute; bottom: 36px; left: 44px; right: 44px; text-align: center; font-size: 9px; color: #a0aec0; border-top: 1px solid #e2e8f0; padding-top: 12px; }
</style>
</head>
<body>
<div class="page">

    @php $logo = \App\Models\Setting::imageData('company_logo'); @endphp
    <div class="header">
        <div>
            @if ($logo)
                <img src="{{ $logo }}" alt="Ehlom Digital" style="max-height:50px;max-width:200px;">
            @else
                <div class="brand-name">Ehlom Digital</div>
                <div class="brand-tag">Web Design · Hosting · Digital Solutions</div>
                <div class="brand-contact">ehlomdigital.com</div>
            @endif
        </div>
        <div>
            <div class="doc-title">AGREEMENT</div>
            <div class="doc-type">{{ $agreement->type === 'ecommerce' ? 'E-commerce' : ucfirst($agreement->type) }}</div>
        </div>
    </div>

    <table class="meta">
        <tr>
            <td>
                <div class="label">Agreement</div>
                <div class="value">{{ $agreement->title }}</div>
            </td>
            <td style="text-align:right;">
                <div class="label">Date</div>
                <div class="value">{{ $agreement->start_date?->format('F j, Y') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label" style="margin-top:8px;">Client</div>
                <div class="value">{{ $agreement->client->name ?? '—' }}</div>
                @if ($agreement->client?->business_name)
                    <div class="body" style="font-size:11px;color:#4a5568;">{{ $agreement->client->business_name }}</div>
                @endif
            </td>
            <td style="text-align:right;">
                <div class="label" style="margin-top:8px;">Term</div>
                <div class="value">
                    {{ $agreement->start_date?->format('M j, Y') }}
                    @if ($agreement->end_date) — {{ $agreement->end_date->format('M j, Y') }} @else onwards @endif
                </div>
            </td>
        </tr>
    </table>

    @if ($agreement->scope)
        <div class="section">
            <h3>Scope of Work</h3>
            <div class="body">{{ $agreement->scope }}</div>
        </div>
    @endif

    <div class="section">
        <h3>Payment</h3>
        <div class="pay-amount">Rs. {{ number_format($agreement->amount, 2) }}</div>
        @if ($agreement->payment_terms)
            <div class="body" style="margin-top:6px;">{{ $agreement->payment_terms }}</div>
        @endif
    </div>

    @if ($agreement->terms_and_conditions)
        <div class="section">
            <h3>Terms &amp; Conditions</h3>
            <div class="body">{{ $agreement->terms_and_conditions }}</div>
        </div>
    @endif

    @php $signature = \App\Models\Setting::imageData('company_signature'); @endphp
    <table class="signature">
        <tr>
            <td><div class="sign-line">Client Signature / Date</div></td>
            <td>
                @if ($signature)
                    <img src="{{ $signature }}" alt="Signature" style="max-height:50px;max-width:180px;margin-bottom:2px;">
                @endif
                <div class="sign-line">Ehlom Digital / Moldo Kipgen / Date</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        This agreement is between {{ $agreement->client->business_name ?: ($agreement->client->name ?? 'the Client') }} and Ehlom Digital.
    </div>
</div>
</body>
</html>
