<?php

namespace App\Http\Controllers;

use App\Models\AddonProduct;
use App\Models\Tenant;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    /**
     * Business Modules catalog (admin visibility page). Modules are code-backed
     * (each is a controller + dashboard views gated by Tenant::hasModule); this
     * page surfaces the whole catalog and how it maps to business types so the
     * admin can SEE what the platform offers without reading config files. New
     * working modules still require a code change first, then appear here.
     *
     * Each business type card shows Free (its default_modules, built into the
     * base plan) vs Paid (AddonProduct rows tagged for that type via
     * AddonProduct::appliesTo, requiring a purchase/activation) - mirrors the
     * "Free / Paid add-ons" split from the original product vision doc.
     */
    public function index(): View
    {
        $modules = config('modules');
        $businessTypes = config('business_types');
        $addons = AddonProduct::where('active', true)->orderBy('name')->get();

        // Inverse map: which business types enable each module by default.
        $usedBy = [];
        foreach ($businessTypes as $typeKey => $type) {
            foreach ($type['default_modules'] ?? [] as $moduleKey) {
                $usedBy[$moduleKey][] = $typeKey;
            }
        }

        // Paid add-ons per business type.
        $paidByType = [];
        foreach ($businessTypes as $typeKey => $type) {
            $paidByType[$typeKey] = $addons->filter(fn ($a) => $a->appliesTo($typeKey))->values();
        }

        // How many live tenants actually have each module enabled right now.
        $liveCounts = [];
        foreach (Tenant::get(['modules']) as $tenant) {
            foreach ($tenant->modules ?? [] as $moduleKey) {
                $liveCounts[$moduleKey] = ($liveCounts[$moduleKey] ?? 0) + 1;
            }
        }

        return view('modules.index', compact('modules', 'businessTypes', 'usedBy', 'liveCounts', 'paidByType'));
    }
}
