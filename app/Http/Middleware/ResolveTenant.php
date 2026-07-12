<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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
            // Covers the bare domain itself (host === $baseDomain) and any
            // non-matching host (e.g. localhost during local testing).
            // web.php's agency-only routes are domain-scoped to a
            // {portalHost} parameter — fill it in here so every
            // route('dashboard')-style call elsewhere in the app doesn't
            // need to pass it explicitly.
            URL::defaults(['portalHost' => $host]);

            return $next($request);
        }

        $subdomain = substr($host, 0, -strlen($suffix));

        // Skip tenant resolution for the agency CRM hosts.
        if (in_array($subdomain, ['portal', 'www', ''], true)) {
            URL::defaults(['portalHost' => $host]);

            return $next($request);
        }

        // Tenant routes are domain-scoped to a {subdomain} parameter — same
        // reasoning as above, for every route('tenant.xxx') call.
        URL::defaults(['subdomain' => $subdomain]);

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
