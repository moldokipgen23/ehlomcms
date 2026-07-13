<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Forgot Password — {{ config('app.name', 'Ehlom OS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased" style="background: linear-gradient(160deg, #0d0f17 0%, #12141d 50%, #181c2b 100%);">

    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="auth-logo-mark">E</div>
                <div>
                    <div class="auth-logo-text">EHLOM OS</div>
                    <div class="auth-logo-sub">Client Dashboard</div>
                </div>
            </div>

            <div class="auth-head">
                <div class="auth-title">Forgot your password?</div>
                <div class="auth-sub">Enter your email and we'll send you a reset link</div>
            </div>

            @if (session('status'))
                <div class="eos-alert-bar success" style="margin-bottom:14px;">
                    <i class="ti ti-check"></i> {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="eos-alert-bar warn" style="margin-bottom:14px;">
                    <i class="ti ti-alert-triangle"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('tenant.password.email') }}">
                @csrf
                <div class="eos-field">
                    <label class="eos-label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="eos-input" required autofocus autocomplete="username">
                </div>
                <button type="submit" class="eos-btn eos-btn-primary auth-submit">
                    <i class="ti ti-mail"></i> Send Reset Link
                </button>
            </form>

            <div style="text-align:center;margin-top:14px;">
                <a href="{{ route('tenant.login') }}" style="font-size:12.5px;color:var(--text-muted);">Back to login</a>
            </div>
        </div>
        <div class="auth-foot">Ehlom Digital &middot; Client Portal</div>
    </div>
</body>
</html>
