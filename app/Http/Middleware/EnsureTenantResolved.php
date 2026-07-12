<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantResolved
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!app(TenantContext::class)->check()) {
            abort(404);
        }

        return $next($request);
    }
}
