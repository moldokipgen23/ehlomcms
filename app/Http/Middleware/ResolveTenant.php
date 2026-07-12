<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $baseDomain = strtolower(config('app.tenant_domain', 'ehlom.com'));

        // Only attempt tenant resolution when the host matches the tenant
        // domain pattern (e.g. *.ehlom.com). Everything else — localhost,
        // production bare domain, portal subdomain — passes through.
        $suffix = '.' . $baseDomain;
        if (!str_ends_with($host, $suffix)) {
            return $next($request);
        }

        $subdomain = substr($host, 0, -strlen($suffix));

        // Skip tenant resolution for the agency CRM hosts.
        if (in_array($subdomain, ['portal', 'www', ''], true)) {
            return $next($request);
        }

        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            abort(404);
        }

        if ($tenant->status === 'suspended') {
            return response('This site is currently unavailable.', 503);
        }

        app(TenantContext::class)->set($tenant);

        return $next($request);
    }
}
