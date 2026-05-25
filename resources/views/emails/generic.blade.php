@php
    use App\Models\Setting;

    // Pull branding for the email shell. The logo is embedded as a base64 data
    // URI so it survives forwards and works without an external host.
    $logoData = Setting::imageData('company_logo');
    $brandName = Setting::get('smtp_from_name') ?: 'Ehlom Digital';
    $fromEmail = Setting::get('smtp_from_address');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $brandName }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e4e4e7;max-width:600px;">
                    {{-- Banner: logo on white background for max compatibility --}}
                    <tr>
                        <td style="background:#ffffff;padding:24px 28px 18px;border-bottom:1px solid #e4e4e7;" align="left">
                            @if ($logoData)
                                <img src="{{ $logoData }}" alt="{{ $brandName }}" style="max-height:48px;max-width:220px;display:block;border:0;outline:none;">
                            @else
                                <span style="color:#0f172a;font-size:20px;font-weight:700;letter-spacing:.3px;">{{ $brandName }}</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:28px 32px;color:#27272a;font-size:14.5px;line-height:1.75;">
                            {!! nl2br(e($body)) !!}
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:18px 32px;background:#fafafa;border-top:1px solid #e4e4e7;color:#71717a;font-size:11.5px;line-height:1.7;">
                            <strong style="color:#3f3f46;">{{ $brandName }}</strong><br>
                            @if ($fromEmail)
                                <a href="mailto:{{ $fromEmail }}" style="color:#71717a;text-decoration:none;">{{ $fromEmail }}</a><br>
                            @endif
                            <span style="color:#a1a1aa;">This is an automated message. Replies are monitored during business hours.</span>
                        </td>
                    </tr>
                </table>

                <div style="color:#a1a1aa;font-size:10.5px;padding:14px 0 0;">
                    © {{ date('Y') }} {{ $brandName }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
