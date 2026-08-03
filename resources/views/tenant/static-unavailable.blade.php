<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }} - Website setup</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f3f6fa; color: #172033; font-family: Inter, system-ui, sans-serif; }
        main { width: min(100%, 620px); padding: 36px; border: 1px solid #dce5f0; border-top: 4px solid #2563eb; border-radius: 8px; background: #fff; box-shadow: 0 18px 50px rgba(15, 23, 42, .08); }
        .mark { width: 48px; height: 48px; display: grid; place-items: center; margin-bottom: 24px; border-radius: 8px; background: #eaf1ff; color: #2563eb; font-size: 22px; font-weight: 800; }
        h1 { margin: 0 0 10px; font-size: clamp(26px, 5vw, 38px); line-height: 1.15; }
        p { margin: 0; color: #64748b; font-size: 16px; line-height: 1.7; }
    </style>
</head>
<body>
    <main>
        <div class="mark">{{ strtoupper(substr($tenant->name, 0, 1)) }}</div>
        <h1>{{ $tenant->name }} is being prepared.</h1>
        <p>The approved website design has not been assigned yet. Please check back shortly.</p>
    </main>
</body>
</html>
