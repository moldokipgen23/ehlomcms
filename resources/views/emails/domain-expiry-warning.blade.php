<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Inter,sans-serif;background:#f8fafc;padding:40px;">
    <div style="max-width:500px;margin:0 auto;background:white;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div style="background:#0f172a;padding:24px;text-align:center;">
            <h1 style="color:white;font-size:20px;margin:0;">Domain Expiry Warning</h1>
        </div>
        <div style="padding:32px;">
            <p style="color:#1e293b;font-size:15px;">Hi {{ $client_name }},</p>
            <p style="color:#475569;font-size:14px;line-height:1.7;">
                Your domain <strong style="color:#0f172a;">{{ $domain_name }}</strong> is expiring in
                <strong style="color:#ef4444;">{{ $days_left }} days</strong> on <strong>{{ $expiry_date }}</strong>.
            </p>

            <div style="background:#f1f5f9;border-radius:8px;padding:16px;margin:20px 0;">
                <table style="width:100%;font-size:13px;color:#475569;">
                    <tr><td style="padding:4px 0;">Domain:</td><td style="text-align:right;font-weight:600;color:#0f172a;">{{ $domain_name }}</td></tr>
                    <tr><td style="padding:4px 0;">Registrar:</td><td style="text-align:right;">{{ $registrar }}</td></tr>
                    <tr><td style="padding:4px 0;">Expiry Date:</td><td style="text-align:right;font-weight:600;color:#ef4444;">{{ $expiry_date }}</td></tr>
                    <tr><td style="padding:4px 0;">Renewal Cost:</td><td style="text-align:right;font-weight:600;color:#0f172a;">{{ $renewal_cost }}</td></tr>
                </table>
            </div>

            <p style="color:#475569;font-size:14px;line-height:1.7;">
                Please renew your domain before it expires to avoid any disruption to your website and email services.
            </p>

            <div style="text-align:center;margin:24px 0;">
                <a href="mailto:{{ config('mail.from.address') }}" style="display:inline-block;background:#14b8a6;color:white;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;">Contact Support</a>
            </div>
        </div>
        <div style="background:#f1f5f9;padding:16px;text-align:center;font-size:11px;color:#94a3b8;">
            {{ config('app.name') }} &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
