<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AdminImpersonateController extends Controller
{
    /**
     * "Login as Client" cannot use auth()->login() directly here: the
     * admin's browser is on portal.ehlom.com, but the tenant dashboard
     * lives on {subdomain}.ehlom.com - a different host. Session cookies in
     * this app are host-only (SESSION_DOMAIN=null, matching
     * TenantAuthenticate's own defense-in-depth check that a session's
     * tenant_id must match the resolved subdomain), so a session
     * established here would never be sent by the browser to the tenant
     * subdomain - the admin would just land on the tenant's own login page.
     *
     * Instead this hands off via a short-lived, single-purpose Laravel
     * signed URL to a route on the tenant's own domain (see
     * Tenant\TenantImpersonateController::consume), which performs the
     * actual login in the correct host context. The signature is verified
     * server-side against APP_KEY and needs no session of its own to work
     * across domains.
     */
    public function loginAsTenant(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = User::where('tenant_id', $tenant->id)->first();

        if (!$user) {
            return back()->with('error', 'No owner account found for this tenant.');
        }

        $tenantDomain = config('app.tenant_domain', 'ehlom.com');
        $tenantHost = $tenant->subdomain . '.' . $tenantDomain;

        $expires = now()->addMinutes(2)->getTimestamp();

        $params = [
            'subdomain' => $tenant->subdomain,
            'user_id' => $user->id,
            'admin_id' => auth()->id(),
            'expires' => $expires,
        ];

        $baseUrl = "https://{$tenantHost}/dashboard/impersonate/consume";
        $queryString = http_build_query($params);
        $urlToSign = $baseUrl . '?' . $queryString;

        $signature = hash_hmac('sha256', $urlToSign, config('app.key'));
        $signedUrl = $urlToSign . '&signature=' . $signature;

        AuditLog::log('impersonation_started', "Impersonated tenant {$tenant->name}", 'tenant', $tenant->id);

        return redirect()->away($signedUrl);
    }
}
