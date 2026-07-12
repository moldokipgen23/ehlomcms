<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Illuminate\Auth\Middleware\Authenticate;

class TenantAuthenticate extends Authenticate
{
    protected function redirectTo($request): ?string
    {
        if (app(TenantContext::class)->check()) {
            return route('tenant.login');
        }

        return parent::redirectTo($request);
    }
}
