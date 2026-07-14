<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $theme->name }} — Theme Preview</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|syne:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .preview-bar { background: #0f172a; color: white; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
        .preview-bar h3 { font-size: 14px; font-weight: 600; }
        .preview-bar .badge { background: #14b8a6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; }
        .hero { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 80px 40px; text-align: center; }
        .hero h1 { font-family: 'Syne', sans-serif; font-size: 42px; font-weight: 700; margin-bottom: 16px; }
        .hero p { font-size: 18px; opacity: 0.8; max-width: 600px; margin: 0 auto; }
        .section { padding: 60px 40px; max-width: 1200px; margin: 0 auto; }
        .section h2 { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 600; margin-bottom: 24px; text-align: center; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card-img { height: 160px; background: linear-gradient(135deg, #e0f2fe, #bae6fd); display: flex; align-items: center; justify-content: center; }
        .card-img i { font-size: 40px; color: #0ea5e9; }
        .card-body { padding: 16px; }
        .card-body h3 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
        .card-body p { font-size: 13px; color: #64748b; }
        .card-body .price { font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 8px; }
        .footer { background: #0f172a; color: #94a3b8; padding: 40px; text-align: center; font-size: 13px; }
    </style>
</head>
<body>
    <div class="preview-bar">
        <div><h3>{{ $theme->name }} <span class="badge">PREVIEW</span></h3></div>
        <div style="font-size:12px;opacity:0.7;">Demo data — your content will appear here</div>
    </div>

    <div class="hero">
        <h1>{{ $demoTenant->name }}</h1>
        <p>{{ $demoTenant->about_text }}</p>
    </div>

    <div class="section">
        <h2>Our Products</h2>
        <div class="grid">
            @for ($i = 1; $i <= 3; $i++)
                <div class="card">
                    <div class="card-img"><i class="ti ti-package"></i></div>
                    <div class="card-body">
                        <h3>Product {{ $i }}</h3>
                        <p>Sample product description goes here.</p>
                        <div class="price">₹{{ number_format($i * 499, 0) }}</div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <div class="section" style="background:white;">
        <h2>About Us</h2>
        <p style="text-align:center;max-width:700px;margin:0 auto;color:#64748b;line-height:1.8;">
            {{ $demoTenant->about_text }}
        </p>
    </div>

    <div class="footer">
        <p>{{ $demoTenant->name }} &copy; {{ date('Y') }} — Powered by Ehlom</p>
        <p style="margin-top:4px;">{{ $demoTenant->contact_email }} | {{ $demoTenant->contact_phone }}</p>
    </div>
</body>
</html>
