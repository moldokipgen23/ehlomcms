<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank You — Ehlom Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background:
                radial-gradient(900px 500px at 12% -5%, rgba(79,142,247,.08), transparent 60%),
                radial-gradient(800px 480px at 95% 0%, rgba(139,111,247,.08), transparent 55%),
                var(--bg-base);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .thank-card {
            text-align: center;
            background: linear-gradient(170deg, #1c2135 0%, var(--bg-card) 65%);
            border: 1px solid var(--border-card); border-radius: 14px;
            padding: 48px 36px; max-width: 420px; margin: 20px;
        }
        .thank-icon { width: 60px; height: 60px; border-radius: 50%; background: #041f18; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .thank-icon i { font-size: 28px; color: var(--accent-teal); }
        .thank-card h1 { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
        .thank-card p { font-size: 13px; color: var(--text-muted); line-height: 1.6; }
        .thank-card a { display: inline-flex; align-items: center; gap: 6px; margin-top: 20px; padding: 10px 20px; background: #1a2240; border: 1px solid #2a3560; border-radius: 8px; color: var(--accent-blue); text-decoration: none; font-size: 13px; font-weight: 600; }
        .thank-card a:hover { border-color: #3a4570; }
    </style>
</head>
<body>
    <div class="thank-card">
        <div class="thank-icon"><i class="ti ti-circle-check"></i></div>
        <h1>Thank You!</h1>
        <p>We've received your inquiry and will review it shortly.<br>Our team will get back to you within 24 hours.</p>
        <a href="/"><i class="ti ti-arrow-left"></i> Back to Home</a>
    </div>
</body>
</html>
