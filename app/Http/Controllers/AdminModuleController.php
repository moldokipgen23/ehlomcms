<?php

namespace App\Http\Controllers;

use App\Models\BusinessTypeModule;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    private function moduleKeysForFeature(string $businessType, string $featureKey): array
    {
        $features = array_merge(
            config("modules.bundles.{$businessType}.free", []),
            config("modules.bundles.{$businessType}.pro", []),
            config("modules.bundles.{$businessType}.premium", []),
        );

        foreach ($features as $feature) {
            if (($feature['key'] ?? null) === $featureKey) {
                return $feature['keys'] ?? [$featureKey];
            }
        }

        return [$featureKey];
    }

    private function moduleKeysForFeatures(array $features): array
    {
        $keys = [];

        foreach ($features as $feature) {
            foreach (($feature['keys'] ?? [$feature['key']]) as $moduleKey) {
                $keys[] = $moduleKey;
            }
        }

        return array_values(array_unique($keys));
    }

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

        $moduleAssignments = BusinessTypeModule::assignmentsFor($businessType);

        return view('modules.show', compact('bundle', 'businessType', 'businessTypes', 'tenants', 'selectedTenant', 'moduleAssignments'));
    }

    public function updatePricing(Request $request, string $businessType): RedirectResponse
    {
        abort_unless(array_key_exists($businessType, config('business_types')), 404);

        $data = $request->validate([
            'feature_key' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', Rule::in(['one_time', 'monthly', 'quarterly', 'yearly'])],
        ]);

        $features = array_merge(
            config("modules.bundles.{$businessType}.pro", []),
            config("modules.bundles.{$businessType}.premium", []),
        );
        $feature = collect($features)->firstWhere('key', $data['feature_key']);

        if (!$feature || !empty($feature['future'])) {
            return back()->with('error', 'This feature is not available for pricing.');
        }

        foreach ($feature['keys'] ?? [$feature['key']] as $moduleKey) {
            BusinessTypeModule::updateOrCreate(
                ['business_type' => $businessType, 'module_key' => $moduleKey],
                [
                    'status' => 'paid',
                    'price' => $data['price'],
                    'billing_cycle' => $data['billing_cycle'],
                ],
            );
        }

        return redirect()->route('modules.show', $businessType)->with('success', $feature['name'] . ' pricing updated.');
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
        $moduleKeys = $this->moduleKeysForFeature($tenant->site_type, $featureKey);
        $enabled = collect($moduleKeys)->every(fn ($key) => $tenant->hasModule($key));

        if ($enabled) {
            $modules = array_values(array_diff($modules, $moduleKeys));
        } else {
            $modules = array_values(array_unique(array_merge($modules, $moduleKeys)));
        }

        $tenant->modules = $modules;
        $tenant->save();

        $status = $enabled ? 'disabled' : 'enabled';

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
        $allFeatures = array_merge(
            $allFeatures,
            array_filter(config("modules.bundles.{$businessType}.pro", []), fn ($feature) => empty($feature['future'])),
        );
        $featureKeys = $this->moduleKeysForFeatures($allFeatures);

        if ($action === 'on') {
            $tenant->modules = array_unique(array_merge(
                $tenant->modules ?? [],
                $featureKeys
            ));
        } else {
            $tenant->modules = array_values(array_diff($tenant->modules ?? [], $featureKeys));
        }

        $tenant->save();

        return redirect()->route('modules.show', ['businessType' => $tenant->site_type, 'tenant' => $tenant->id])->with('success', ucfirst($action === 'on' ? 'Enabled' : 'Disabled') . ' all features for ' . $tenant->name);
    }
}
