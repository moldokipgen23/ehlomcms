<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AddonProduct;
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

        $allAddons = AddonProduct::where('active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn (AddonProduct $addon) => $addon->appliesTo($tenant->site_type));

        // Vertical-specific catalog items (tagged to exactly ONE business
        // type) render as their own "<Type> Features" section, kept separate
        // from the platform-wide marketplace below it - same page, no new
        // nav entry, per product decision 2026-07-16. "Platform-wide" here
        // means tagged to zero or multiple types, not literally empty - the
        // existing add-ons (WhatsApp, AI Agent, etc) are each tagged to
        // several business types, not left blank.
        $verticalAddons = $allAddons->filter(fn (AddonProduct $addon) => count($addon->business_types ?? []) === 1)->keyBy('key');
        $platformAddons = $allAddons->filter(fn (AddonProduct $addon) => count($addon->business_types ?? []) !== 1)->keyBy('key');

        $records = TenantAddon::where('tenant_id', $tenant->id)->get()->keyBy('addon_key');

        // Free bundle items are informational only (no purchase, no
        // TenantAddon record) - they mirror the tenant's real module state
        // so "Included" here always matches whether the feature actually
        // works, not a static claim.
        $freeBundle = collect(config("modules.bundles.{$tenant->site_type}.free", []))
            ->map(fn ($feat) => array_merge($feat, ['active' => $tenant->hasModule($feat['key'])]));

        return view('tenant.addons.index', compact('tenant', 'freeBundle', 'verticalAddons', 'platformAddons', 'records'));
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

        if (!AddonProduct::where('key', $addonKey)->where('active', true)->exists()) {
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
