<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class TenantForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('tenant.auth.forgot-password');
    }

    /**
     * Uses Laravel's default password broker - safe to share with the
     * agency's own forgot-password flow because `users.email` is globally
     * unique (see 2026_07_13_000002_add_tenant_id_to_users_table.php), so
     * there's no ambiguity about which user a reset link targets. Where the
     * link actually points (tenant subdomain vs portal) is decided in
     * AppServiceProvider's ResetPassword::createUrlUsing() callback, based
     * on whether the matched user has a tenant_id.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
