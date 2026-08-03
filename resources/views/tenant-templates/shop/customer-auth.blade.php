<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }} — Customer Account</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div style="max-width:900px;margin:40px auto;padding:0 18px;">
    <a href="{{ route('tenant.home') }}" style="color:var(--text-muted);text-decoration:none;">← Back to store</a>
    <h1 style="margin:18px 0;color:var(--text-primary);">Customer Account</h1>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;">
        <form method="POST" action="{{ route('tenant.customer.login') }}" class="eos-card" style="padding:20px;">@csrf
            <h2 style="font-size:18px;margin-bottom:14px;">Login</h2>
            <div class="eos-field"><label class="eos-label">Email</label><input type="email" name="email" class="eos-input" required></div>
            <div class="eos-field"><label class="eos-label">Password</label><input type="password" name="password" class="eos-input" required></div>
            <button class="eos-btn eos-btn-primary">Login</button>
        </form>
        <form method="POST" action="{{ route('tenant.customer.register') }}" class="eos-card" style="padding:20px;">@csrf
            <h2 style="font-size:18px;margin-bottom:14px;">Create account</h2>
            <div class="eos-field"><label class="eos-label">Name</label><input name="name" class="eos-input" required></div>
            <div class="eos-field"><label class="eos-label">Email</label><input type="email" name="email" class="eos-input" required></div>
            <div class="eos-field"><label class="eos-label">Phone</label><input name="phone" class="eos-input"></div>
            <div class="eos-field"><label class="eos-label">Password</label><input type="password" name="password" class="eos-input" required></div>
            <button class="eos-btn eos-btn-primary">Create Account</button>
        </form>
    </div>
</div>
</body>
</html>
