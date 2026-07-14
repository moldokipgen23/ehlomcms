<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Inter,sans-serif;background:#f8fafc;padding:40px;">
    <div style="max-width:500px;margin:0 auto;background:white;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div style="background:#0f172a;padding:24px;text-align:center;">
            <h1 style="color:white;font-size:20px;margin:0;">Complete Your Setup</h1>
        </div>
        <div style="padding:32px;">
            <p style="color:#1e293b;font-size:15px;">Hi {{ $client_name }},</p>
            <p style="color:#475569;font-size:14px;line-height:1.7;">
                Your site <strong style="color:#0f172a;">{{ $tenant_name }}</strong> is almost ready!
                You've completed step {{ $current_step }} of 5 ({{ $current_step_name }}).
            </p>

            <div style="background:#f1f5f9;border-radius:8px;padding:16px;margin:20px 0;">
                <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:8px;">Progress</div>
                <div style="background:#e2e8f0;border-radius:4px;height:8px;overflow:hidden;">
                    <div style="background:#14b8a6;height:100%;width:{{ ($current_step / 5) * 100 }}%;"></div>
                </div>
                <div style="font-size:11px;color:#94a3b8;margin-top:6px;">Step {{ $current_step }} of 5</div>
            </div>

            <p style="color:#475569;font-size:14px;line-height:1.7;">
                Next step: <strong style="color:#14b8a6;">{{ $next_step_name }}</strong>
            </p>

            <div style="text-align:center;margin:24px 0;">
                <a href="{{ $login_url }}" style="display:inline-block;background:#14b8a6;color:white;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;">Continue Setup</a>
            </div>
        </div>
        <div style="background:#f1f5f9;padding:16px;text-align:center;font-size:11px;color:#94a3b8;">
            {{ config('app.name') }} &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
