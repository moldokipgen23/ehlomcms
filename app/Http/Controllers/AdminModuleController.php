<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    /**
     * Show all business type bundles with tier summary.
     */
    public function index(): View
    {
        $bundles = config('modules.bundles') ?? [];
        $businessTypes = config('business_types') ?? [];

        $tenantCounts = Tenant::select('site_type')
            ->selectRaw('COUNT(*) as cnt')
            ->where('status', 'active')
            ->groupBy('site_type')
            ->pluck('cnt', 'site_type');

        return view('modules.index', compact('bundles', 'businessTypes', 'tenantCounts'));
    }

    /**
     * Show feature breakdown for a business type with toggle switches.
     */
    public function show(Request $request, string $businessType): View
    {
        abort_unless(array_key_exists($businessType, config('business_types')), 404);

        $bundles = config('modules.bundles');
        $bundle = $bundles[$businessType] ?? null;
        $businessTypes = config('business_types');

        $tenants = Tenant::where('site_type', $businessType)
            ->where('status', 'active')
            ->with('client')
            ->orderBy('name')
            ->get();

        $selectedTenant = null;
        if ($request->query('tenant')) {
            $selectedTenant = $tenants->firstWhere('id', $request->query('tenant'));
        }

        return view('modules.show', compact('bundle', 'businessType', 'businessTypes', 'tenants', 'selectedTenant'));
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
            $modules = array_values(array_diff($modules, [$featureKey]));
        } else {
            $modules[] = $featureKey;
        }

        $tenant->modules = $modules;
        $tenant->save();

        $status = in_array($featureKey, $modules) ? 'enabled' : 'disabled';

        return redirect()->route('modules.show', ['businessType' => $tenant->site_type, 'tenant' => $tenant->id])->with('success', 'Feature "' . str_replace('_', ' ', $featureKey) . '" ' . $status . ' for ' . $tenant->name);
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
        $allFeatures = config("modules.bundles.{$businessType}.free", []);
        $allFeatures = array_merge($allFeatures, config("modules.bundles.{$businessType}.pro", []));

        if ($action === 'on') {
            $tenant->modules = array_unique(array_merge(
                $tenant->modules ?? [],
                array_column($allFeatures, 'key')
            ));
        } else {
            $featureKeys = array_column($allFeatures, 'key');
            $tenant->modules = array_values(array_diff($tenant->modules ?? [], $featureKeys));
        }

        $tenant->save();

        return redirect()->route('modules.show', ['businessType' => $tenant->site_type, 'tenant' => $tenant->id])->with('success', ucfirst($action === 'on' ? 'Enabled' : 'Disabled') . ' all features for ' . $tenant->name);
    }
}
