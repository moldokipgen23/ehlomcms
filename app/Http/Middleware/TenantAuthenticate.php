<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Auth;

class TenantAuthenticate extends Authenticate
{
    protected function redirectTo($request): ?string
    {
        if (app(TenantContext::class)->check()) {
            return route('tenant.login');
        }

        return parent::redirectTo($request);
    }

    /**
     * Defense in depth beyond parent::handle()'s plain "is anyone logged in"
     * check: a session authenticated for one tenant must never be honored on
     * a different tenant's subdomain, even if the raw session cookie were
     * somehow replayed there directly (bypassing normal browser cookie-domain
     * scoping — e.g. a forged Cookie header). Verify the authenticated
     * user's tenant_id actually matches the tenant resolved for this
     * request before deferring to the parent auth check.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $tenant = app(TenantContext::class)->get();

        if ($tenant && Auth::check() && (int) Auth::user()->tenant_id !== (int) $tenant->id) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();

            return redirect()->route('tenant.login');
        }

        return parent::handle($request, $next, ...$guards);
    }
}
