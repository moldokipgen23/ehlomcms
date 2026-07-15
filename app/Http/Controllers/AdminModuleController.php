<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    /**
     * Show all tenants grouped by business type, with feature toggle grid.
     */
    public function index(Request $request): View
    {
        $businessTypes = config('modules.business_types');

        // Which business type tab we're on (default: first)
        $activeType = $request->query('type', array_key_first($businessTypes));

        // Tenants for this business type
        $tenants = Tenant::where('site_type', $activeType)
            ->with('client')
            ->orderBy('name')
            ->get();

        return view('modules.index', compact('businessTypes', 'activeType', 'tenants'));
    }

    /**
     * Toggle a feature on/off for a specific tenant.
     */
    public function toggle(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'feature_key' => 'required|string',
        ]);

        $featureKey = $request->input('feature_key');
        $modules = $tenant->modules ?? [];

        if (in_array($featureKey, $modules)) {
            // Turn off
            $modules = array_values(array_diff($modules, [$featureKey]));
        } else {
            // Turn on
            $modules[] = $featureKey;
        }

        $tenant->modules = $modules;
        $tenant->save();

        return redirect()->back()->with('success', 'Feature "' . $featureKey . '" ' . (in_array($featureKey, $modules) ? 'enabled' : 'disabled') . ' for ' . $tenant->name);
    }

    /**
     * Bulk toggle — enable/disable all features of a type for a tenant.
     */
    public function bulkToggle(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'business_type' => 'required|string',
            'action' => 'required|in:on,off',
        ]);

        $businessType = $request->input('business_type');
        $action = $request->input('action');
        $allFeatures = config("modules.business_types.{$businessType}.features", []);

        if ($action === 'on') {
            // Enable all toggleable features
            $tenant->modules = array_unique(array_merge(
                $tenant->modules ?? [],
                array_column(array_filter($allFeatures, fn ($f) => $f['toggleable'] ?? false), 'key')
            ));
        } else {
            // Disable all features for this type
            $featureKeys = array_column($allFeatures, 'key');
            $tenant->modules = array_values(array_diff($tenant->modules ?? [], $featureKeys));
        }

        $tenant->save();

        return redirect()->back()->with('success', ucfirst($action === 'on' ? 'enabled' : 'disabled') . ' all features for ' . $tenant->name);
    }
}
