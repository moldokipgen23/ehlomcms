<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    /**
     * Feature Bundles — shows what each business type includes at
     * Free, Pro, and Premium tiers. Replaces the old generic toggle system.
     */
    public function index(): View
    {
        $bundles = config('modules.bundles');
        $businessTypes = config('business_types');

        // How many live tenants use each business type
        $tenantCounts = Tenant::select('site_type')
            ->where('status', 'active')
            ->groupBy('site_type')
            ->pluck('count', 'site_type');

        return view('modules.index', compact('bundles', 'businessTypes', 'tenantCounts'));
    }

    /**
     * Show details for a specific business type bundle.
     */
    public function show(string $businessType): View
    {
        abort_unless(array_key_exists($businessType, config('business_types')), 404);

        $bundles = config('modules.bundles');
        $bundle = $bundles[$businessType] ?? null;
        $businessTypes = config('business_types');

        // Which tenants are using this business type
        $tenants = Tenant::where('site_type', $businessType)
            ->where('status', 'active')
            ->with('client')
            ->orderBy('name')
            ->get();

        return view('modules.show', compact('bundle', 'businessType', 'businessTypes', 'tenants'));
    }
}
