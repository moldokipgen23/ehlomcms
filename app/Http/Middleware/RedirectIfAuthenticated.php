<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated as BaseRedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated extends BaseRedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // On a tenant subdomain, redirect to the tenant dashboard.
                // The default implementation calls route('dashboard') which
                // requires portalHost and crashes on tenant subdomains.
                if (app(TenantContext::class)->check()) {
                    return redirect()->route('tenant.dashboard');
                }

                return $this->redirectTo($request);
            }
        }

        return $next($request);
    }
}
