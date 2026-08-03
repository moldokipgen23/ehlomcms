<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTenantDeliveryMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app(TenantContext::class)->get();

        if (!$tenant || $tenant->site_mode !== 'static' || $request->routeIs('tenant.dashboard')) {
            return $next($request);
        }

        if (!$request->isMethodSafe()) {
            abort(403, 'Content management is not available for this static website.');
        }

        return redirect()->route('tenant.dashboard')->with(
            'error',
            'This is a static approved website. Contact Ehlom to request design or content changes.'
        );
    }
}
