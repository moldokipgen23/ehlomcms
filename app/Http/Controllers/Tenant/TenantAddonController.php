<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantAddon;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantAddonController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        $addons = config('addons');
        $records = TenantAddon::where('tenant_id', $tenant->id)->get()->keyBy('addon_key');

        return view('tenant.addons.index', compact('tenant', 'addons', 'records'));
    }

    /**
     * A tenant can REQUEST an add-on or CANCEL a pending request / disable an
     * already-active one - they can never flip anything straight to active
     * themselves. Activation only happens via
     * AdminTenantAddonController::activate(), after the agency has confirmed
     * payment (offline for now - see docs/SAAS_REQUIREMENTS_AND_GAPS.md).
     */
    public function toggle(Request $request, string $subdomain, string $addonKey): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $addons = config('addons');
        if (!isset($addons[$addonKey])) {
            return back()->with('error', 'Add-on not found.');
        }

        $existing = TenantAddon::where('tenant_id', $tenant->id)
            ->where('addon_key', $addonKey)
            ->first();

        if ($existing && in_array($existing->status, ['active', 'pending'], true)) {
            $message = $existing->status === 'active' ? 'Add-on disabled.' : 'Request cancelled.';
            $existing->update(['status' => 'inactive']);

            return back()->with('success', $message);
        }

        TenantAddon::updateOrCreate(
            ['tenant_id' => $tenant->id, 'addon_key' => $addonKey],
            ['status' => 'pending', 'activated_at' => null],
        );

        return back()->with('success', 'Requested. The agency will confirm payment and activate this add-on.');
    }
}
