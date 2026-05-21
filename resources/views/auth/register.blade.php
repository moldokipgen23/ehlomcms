<x-guest-layout>
    <div class="auth-head">
        <div class="auth-title">Create your account</div>
        <div class="auth-sub">Set up your admin login</div>
    </div>

    @if ($errors->any())
        <div class="eos-alert-bar warn" style="margin-bottom:14px;">
            <i class="ti ti-alert-triangle"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="eos-field">
            <label class="eos-label" for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="eos-input" required autofocus autocomplete="name">
        </div>

        <div class="eos-field">
            <label class="eos-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="eos-input" required autocomplete="username">
        </div>

        <div class="eos-field">
            <label class="eos-label" for="password">Password</label>
            <input id="password" type="password" name="password"
                   class="eos-input" required autocomplete="new-password">
        </div>

        <div class="eos-field">
            <label class="eos-label" for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="eos-input" required autocomplete="new-password">
        </div>

        <button type="submit" class="eos-btn eos-btn-primary auth-submit">
            <i class="ti ti-user-plus"></i> Register
        </button>

        <a href="{{ route('login') }}" class="auth-link">Already registered? Log in</a>
    </form>
</x-guest-layout>
