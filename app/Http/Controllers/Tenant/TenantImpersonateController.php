<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantImpersonateController extends Controller
{
    /**
     * The receiving end of the cross-domain handoff started in
     * AdminImpersonateController::loginAsTenant. Reached only via a
     * Laravel-signed URL (the 'signed' middleware on this route rejects any
     * request whose signature/expiry doesn't check out - see routes/tenant.php),
     * so no separate auth check is needed to know this request was really
     * authorized by an admin. Runs on the tenant's own subdomain, so
     * auth()->login() here sets a cookie actually scoped to this host -
     * unlike the old implementation, this one really logs the admin in as
     * the tenant.
     */
    public function consume(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $user = User::find($request->query('user_id'));

        // The signature already guarantees this exact user_id+subdomain
        // combination was issued by loginAsTenant and hasn't been tampered
        // with, but re-checking tenant_id here costs nothing and means this
        // controller is safe even if it's ever reached another way.
        if (!$user || !$tenant || (int) $user->tenant_id !== (int) $tenant->id) {
            abort(403);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        session()->put('impersonator_id', $request->query('admin_id'));

        AuditLog::log('impersonation_started', "Admin began impersonating {$tenant->name}", 'tenant', $tenant->id);

        return redirect()->route('tenant.dashboard');
    }

    /**
     * "Leave" only needs to end THIS (tenant-domain) session and send the
     * browser back to portal.ehlom.com - the admin's original admin-panel
     * session on that host was never touched by impersonation (it's a
     * separate host-only cookie), so it's still valid there. No login call
     * needed here at all, cross-domain or otherwise.
     */
    public function leave(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $impersonatorId = session()->pull('impersonator_id');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($impersonatorId && $tenant) {
            AuditLog::log('impersonation_ended', 'Admin stopped impersonating', 'tenant', $tenant->id);
        }

        // Built from tenant_domain rather than APP_URL, matching how
        // ResolveTenant identifies the portal host - APP_URL is
        // environment-dependent and not guaranteed to be the portal host.
        return redirect()->to('https://portal.' . config('app.tenant_domain', 'ehlom.com') . '/tenants');
    }
}
