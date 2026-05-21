<x-guest-layout>
    <div class="auth-head">
        <div class="auth-title">Welcome back</div>
        <div class="auth-sub">Sign in to your admin console</div>
    </div>

    @if (session('status'))
        <div class="eos-alert-bar" style="margin-bottom:14px;">
            <i class="ti ti-circle-check"></i> {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="eos-alert-bar warn" style="margin-bottom:14px;">
            <i class="ti ti-alert-triangle"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="eos-field">
            <label class="eos-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="eos-input" required autofocus autocomplete="username">
        </div>

        <div class="eos-field">
            <label class="eos-label" for="password">Password</label>
            <input id="password" type="password" name="password"
                   class="eos-input" required autocomplete="current-password">
        </div>

        <label class="auth-remember">
            <input type="checkbox" name="remember"> Remember me
        </label>

        <button type="submit" class="eos-btn eos-btn-primary auth-submit">
            <i class="ti ti-login-2"></i> Log In
        </button>

        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="auth-link">Forgot your password?</a>
        @endif
    </form>
</x-guest-layout>
