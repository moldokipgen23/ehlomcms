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
        $activeAddons = $tenant->activeAddons()->pluck('addon_key')->toArray();

        return view('tenant.addons.index', compact('tenant', 'addons', 'activeAddons'));
    }

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

        if ($existing) {
            if ($existing->status === 'active') {
                $existing->update(['status' => 'inactive']);
                return back()->with('success', 'Add-on disabled.');
            } else {
                $existing->update(['status' => 'active', 'activated_at' => now()]);
                return back()->with('success', 'Add-on enabled.');
            }
        }

        TenantAddon::create([
            'tenant_id' => $tenant->id,
            'addon_key' => $addonKey,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        return back()->with('success', 'Add-on enabled.');
    }
}
